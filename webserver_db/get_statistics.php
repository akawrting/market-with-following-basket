<?php
// 오류 표시 설정 (개발 중에는 켜두는 것이 좋음)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 로그 파일에 디버깅 정보 기록 (선택 사항)
function debug_log($message) {
    file_put_contents('statistics_debug.log', date('Y-m-d H:i:s') . ': ' . $message . "\n", FILE_APPEND);
}

// 데이터베이스 연결
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    debug_log('DB 연결 성공');
} catch (PDOException $e) {
    debug_log('DB 연결 실패: ' . $e->getMessage());
    die(json_encode(['error' => '데이터베이스 연결 실패: ' . $e->getMessage()]));
}

// 결과 배열 초기화
$result = [
    'age_stats' => [],
    'gender_stats' => [],
    'popular_items' => [],
    'age_popular_items' => [], // 새로 추가될 데이터
    'gender_popular_items' => [], // 새로 추가될 데이터
    'monthly_sales' => [], // 월별 매출 추이 (기존에 추가했던 부분)
    'summary' => [] // 이 줄 추가
];

// 1. 나이대별 구매 건수 및 총액 통계 (기존 코드)
try {
    $current_year = date('Y');
    $stmt = $conn->query("
        SELECT 
            CASE
                WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) < 20 THEN '10대 미만'
                WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 20 AND 29 THEN '20대'
                WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 30 AND 39 THEN '30대'
                WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 40 AND 49 THEN '40대'
                WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 50 AND 59 THEN '50대'
                ELSE '60대 이상'
            END AS age_group,
            COUNT(DISTINCT ph.purchase_id) as purchase_count,
            SUM(ph.total_amount) as total_spent
        FROM purchase_history ph
        JOIN usertbl u ON ph.userid = u.userid
        WHERE u.yyyymmdd IS NOT NULL
        GROUP BY age_group
        ORDER BY MIN($current_year - SUBSTRING(u.yyyymmdd, 1, 4))
    ");
    $result['age_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("나이대별 통계 쿼리 오류: " . $e->getMessage());
}

// 2. 성별 구매 건수 및 총액 통계 (기존 코드)
try {
    $stmt = $conn->query("
        SELECT 
            CASE 
                WHEN u.gender = 'M' THEN '남성'
                WHEN u.gender = 'F' THEN '여성'
                ELSE '기타'
            END AS gender,
            COUNT(DISTINCT ph.purchase_id) as purchase_count,
            SUM(ph.total_amount) as total_spent
        FROM purchase_history ph
        JOIN usertbl u ON ph.userid = u.userid
        GROUP BY u.gender
    ");
    $result['gender_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("성별 통계 쿼리 오류: " . $e->getMessage());
}

// 3. 인기 상품 TOP 5 (기존 코드)
try {
    $stmt = $conn->query("
        SELECT 
            pi.itemname,
            SUM(pi.itemnum) as total_quantity,
            SUM(pi.totalprice) as total_sales
        FROM purchase_items pi
        GROUP BY pi.itemname
        ORDER BY total_quantity DESC
        LIMIT 5
    ");
    $result['popular_items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("인기 상품 통계 쿼리 오류: " . $e->getMessage());
}

// --- 새로 추가되는 통계 ---

// 4. 나이대별 가장 많이 구매한 상품 (각 나이대별 1위 상품)
try {
    $current_year = date('Y');
    $stmt = $conn->query("
        WITH ranked_items AS (
            SELECT 
                CASE
                    WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) < 20 THEN '10대'
                    WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 20 AND 29 THEN '20대'
                    WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 30 AND 39 THEN '30대'
                    WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 40 AND 49 THEN '40대'
                    WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 50 AND 59 THEN '50대'
                    ELSE '60대 이상'
                END AS age_group,
                pi.itemname,
                SUM(pi.itemnum) as total_quantity,
                ROW_NUMBER() OVER(PARTITION BY 
                    CASE
                        WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) < 20 THEN '10대'
                        WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 20 AND 29 THEN '20대'
                        WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 30 AND 39 THEN '30대'
                        WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 40 AND 49 THEN '40대'
                        WHEN ($current_year - SUBSTRING(u.yyyymmdd, 1, 4)) BETWEEN 50 AND 59 THEN '50대'
                        ELSE '60대 이상'
                    END
                ORDER BY SUM(pi.itemnum) DESC) as rn
            FROM purchase_items pi
            JOIN purchase_history ph ON pi.purchase_id = ph.purchase_id
            JOIN usertbl u ON ph.userid = u.userid
            WHERE u.yyyymmdd IS NOT NULL
            GROUP BY age_group, pi.itemname
        )
        SELECT age_group, itemname, total_quantity
        FROM ranked_items
        WHERE rn = 1
        ORDER BY age_group
    ");
    
    $result['age_popular_items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("나이대별 인기 상품 쿼리 오류: " . $e->getMessage());
    $result['age_popular_items'] = [];
}

// 5. 성별 가장 많이 구매한 상품 (각 성별별 1위 상품)
try {
    $stmt = $conn->query("
        WITH ranked_items AS (
            SELECT 
                CASE 
                    WHEN u.gender = 'M' THEN '남성'
                    WHEN u.gender = 'F' THEN '여성'
                    ELSE '기타'
                END AS gender,
                pi.itemname,
                SUM(pi.itemnum) as total_quantity,
                ROW_NUMBER() OVER(PARTITION BY u.gender ORDER BY SUM(pi.itemnum) DESC) as rn
            FROM purchase_items pi
            JOIN purchase_history ph ON pi.purchase_id = ph.purchase_id
            JOIN usertbl u ON ph.userid = u.userid
            GROUP BY gender, pi.itemname
        )
        SELECT gender, itemname, total_quantity
        FROM ranked_items
        WHERE rn = 1
        ORDER BY gender
    ");
    
    $result['gender_popular_items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("성별별 인기 상품 쿼리 오류: " . $e->getMessage());
    $result['gender_popular_items'] = [];
}


// 6. 월별 매출 추이 (최근 6개월) - 기존 코드
try {
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(ph.purchase_date, '%Y-%m') as month,
            SUM(ph.total_amount) as monthly_sales,
            COUNT(DISTINCT ph.purchase_id) as order_count
        FROM purchase_history ph
        WHERE ph.purchase_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $result['monthly_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("월별 매출 쿼리 오류: " . $e->getMessage());
    $result['monthly_sales'] = [];
}
// 7. 요약 통계 (총 구매 건수, 총 구매 금액, 총 사용자 수)
try {
        // 총 매출, 총 구매 건수, 총 회원 수
        $stmt_summary = $conn->query("
            SELECT 
                (SELECT SUM(total_amount) FROM purchase_history) AS total_sales,
                (SELECT COUNT(purchase_id) FROM purchase_history) AS total_purchases,
                (SELECT COUNT(userid) FROM usertbl) AS total_users
        ");
        $result['summary'] = $stmt_summary->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("요약 통계 쿼리 오류: " . $e->getMessage());
        $result['summary'] = [];
    }


// JSON 형태로 결과 반환
header('Content-Type: application/json');
echo json_encode($result);
?>
