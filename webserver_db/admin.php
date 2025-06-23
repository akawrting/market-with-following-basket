<?php
// 세션 시작
session_start();

// 관리자 비밀번호 설정 (실제로는 더 안전한 방법으로 저장해야 함)
$admin_password = "admin1234";

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $is_logged_in = true;
    } else {
        $login_error = "비밀번호가 일치하지 않습니다.";
    }
}

// 로그아웃 처리
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    header("Location: admin.php");
    exit;
}

// 데이터베이스 연결
$host = "127.0.0.1";
$db = "famarket";
$user = "famarket";
$pass = "qpalzm1029!";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 페이지</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .login-form {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .error {
            color: #e74c3c;
            margin-top: 10px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            background-color: #f1f1f1;
            border: none;
            padding: 12px 20px;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background-color: #4CAF50;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .detail-btn {
            background-color: #3498db;
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .detail-btn:hover {
            background-color: #2980b9;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 80%;
            max-width: 800px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .search-box {
            margin-bottom: 20px;
            display: flex;
        }
        
        .search-box input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
        }
        
        .search-box button {
            border-radius: 0 5px 5px 0;
        }
        .inventory-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
        }

        .inventory-btn:hover {
            background-color: #2980b9;
        }
        
        /* 기존 스타일에 추가 */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* 2열 그리드 */
        gap: 15px;
        margin-top: 20px;
    }

    .stats-card {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .stats-card.wide {
        grid-column: span 2; /* 2열을 모두 차지 */
    }

    .popular-items-list {
        margin-top: 15px;
    }

    .popular-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .popular-item:last-child {
        border-bottom: none;
    }

    .popular-item-age, .popular-item-gender {
        font-weight: 600;
        color: #4CAF50; /* 강조 색상 */
        min-width: 80px; /* 정렬을 위해 최소 너비 지정 */
    }

    .popular-item-name {
        flex-grow: 1;
        margin: 0 10px;
    }

    .popular-item-quantity {
        font-weight: 500;
        color: #555;
        min-width: 50px; /* 정렬을 위해 최소 너비 지정 */
        text-align: right;
    }

    /* 반응형 디자인 */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr; /* 모바일에서는 1열로 변경 */
            gap: 10px; /* 모바일 간격도 줄이기 */
        }
        .stats-card.wide {
            grid-column: span 1; /* 모바일에서는 1열로 변경 */
        }
    }
    /* 기존 스타일에 추가 */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* 반응형 3열 */
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: linear-gradient(45deg, #ffffff, #f8f9fa);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .card-icon {
        font-size: 40px;
        color: #6c5ce7; /* 보라색 계열 */
        margin-right: 20px;
        background-color: rgba(108, 92, 231, 0.1);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-content {
        flex-grow: 1;
    }

    .card-title {
        font-size: 16px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .card-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }

    /* 아이콘 색상 커스터마이징 (선택 사항) */
    .summary-card:nth-child(1) .card-icon { color: #2dce89; background-color: rgba(45, 206, 137, 0.1); } /* 초록색 */
    .summary-card:nth-child(2) .card-icon { color: #f5365c; background-color: rgba(245, 54, 92, 0.1); } /* 빨간색 */
    .summary-card:nth-child(3) .card-icon { color: #11cdef; background-color: rgba(17, 205, 239, 0.1); } /* 하늘색 */

    /* 반응형 디자인 (모바일에서 1열) */
    @media (max-width: 768px) {
        .summary-cards {
            grid-template-columns: 1fr;
        }
        .summary-card {
            flex-direction: column;
            text-align: center;
        }
        .card-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }
    }



    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_logged_in): ?>
            <!-- 로그인 폼 -->
            <div class="login-form">
                <h1>관리자 로그인</h1>
                <?php if (isset($login_error)): ?>
                <p class="error"><?php echo $login_error; ?></p>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="password">관리자 비밀번호:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">로그인</button>
                </form>
            </div>
        <?php else: ?>
            <!-- 관리자 대시보드 -->
            <!-- admin.php 파일의 탭 버튼 부분에 추가 -->
            <div class="admin-header">
                <h1>관리자 대시보드</h1>
                <div>
                    <a href="inventory.php" class="inventory-btn">재고 관리</a>
                    <a href="?logout=1" class="logout-btn">로그아웃</a>
                </div>
            </div>
            
            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="openTab('members')">회원 관리</button>
                    <button class="tab-btn" onclick="openTab('purchases')">구매 내역</button>
                     <button class="tab-btn" onclick="openTab('statistics')">구매 통계</button>
                </div>
                
                <!-- 회원 관리 탭 -->
                <div id="members" class="tab-content active">
                    <h2>회원 목록</h2>
                    
                    <div class="search-box">
                        <input type="text" id="memberSearch" placeholder="이름 또는 전화번호로 검색...">
                        <button onclick="searchMembers()">검색</button>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>이름</th>
                                <th>전화번호</th>
                                <th>이메일</th>
                                <th>포인트</th>
                                <th>가입일</th>
                            </tr>
                        </thead>
                        <tbody id="memberTable">
                            <?php
                            // 회원 목록 가져오기
                            $stmt = $conn->query("SELECT * FROM usertbl ORDER BY created_at DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['userid']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['phonenum']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo number_format($row['points']); ?> P</td>
                                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 구매 내역 탭 -->
                <div id="purchases" class="tab-content">
                    <h2>구매 내역</h2>
                    
                    <div class="search-box">
                        <input type="text" id="purchaseSearch" placeholder="회원 ID 또는 날짜로 검색...">
                        <button onclick="searchPurchases()">검색</button>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>구매 ID</th>
                                <th>회원 ID</th>
                                <th>회원 이름</th>
                                <th>결제 금액</th>
                                <th>구매 일시</th>
                                <th>상세 보기</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTable">
                            <?php
                            // 구매 내역 가져오기 (회원 정보와 조인)
                            $stmt = $conn->query("
                                SELECT ph.*, u.username 
                                FROM purchase_history ph
                                JOIN usertbl u ON ph.userid = u.userid
                                ORDER BY ph.purchase_date DESC
                            ");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $row['purchase_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['userid']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo number_format($row['total_amount']); ?>원</td>
                                <td><?php echo date('Y-m-d H:i', strtotime($row['purchase_date'])); ?></td>
                                <td>
                                    <button class="detail-btn" onclick="showPurchaseDetail(<?php echo $row['purchase_id']; ?>)">상세 보기</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- 구매 상세 내역 모달 -->
            <div id="purchaseDetailModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>구매 상세 내역</h2>
                    <div id="purchaseDetailContent"></div>
                </div>
            </div>

            <!-- 구매 통계 탭 -->
            <div id="statistics" class="tab-content">
                <h2>구매 통계 분석</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="card-content">
                            <div class="card-title">총 매출</div>
                            <div class="card-value" id="totalSales">0원</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="card-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="card-content">
                            <div class="card-title">총 구매 건수</div>
                            <div class="card-value" id="totalPurchases">0건</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="card-icon"><i class="fas fa-users"></i></div>
                        <div class="card-content">
                            <div class="card-title">총 회원 수</div>
                            <div class="card-value" id="totalUsers">0명</div>
                        </div>
                    </div>
                </div>
                <h2>구매 통계</h2>
                
                <div class="chart-container">
                    <h3>나이대별 구매 통계</h3>
                    <canvas id="ageChart" style="height: 200px;"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3>성별 구매 통계</h3>
                    <canvas id="genderChart" style="height: 200px;"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3>인기 상품 TOP 5</h3>
                    <canvas id="popularItemsChart" style="height: 200px;"></canvas>
                </div>

                <div class="stats-card wide">
                    <h3>월별 매출 추이 (최근 6개월)</h3>
                    <canvas id="monthlySalesChart" style="height: 200px;"></canvas> <!-- 높이 조절 -->
                </div>
                <!-- 새로 추가되는 섹션 -->
                <div class="stats-card">
                    <h3>나이대별 가장 많이 구매한 상품</h3>
                    <div id="agePopularItems" class="popular-items-list">
                        <!-- JavaScript로 내용이 채워질 곳 -->
                    </div>
                </div>
                
                <div class="stats-card">
                    <h3>성별별 가장 많이 구매한 상품</h3>
                    <div id="genderPopularItems" class="popular-items-list">
                        <!-- JavaScript로 내용이 채워질 곳 -->
                    </div>
            </div>

            <!-- 구매 상세 내역 모달 -->
            <div id="purchaseDetailModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>구매 상세 내역</h2>
                    <div id="purchaseDetailContent"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // 탭 전환 함수
        function openTab(tabName) {
            // 모든 탭 내용 숨기기
            var tabContents = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].style.display = "none";
            }
            
            // 모든 탭 버튼 비활성화
            var tabButtons = document.getElementsByClassName("tab-btn");
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].className = tabButtons[i].className.replace(" active", "");
            }
            
            // 선택한 탭 표시 및 버튼 활성화
            document.getElementById(tabName).style.display = "block";
            event.currentTarget.className += " active";
        }
        
        // 구매 상세 내역 표시 함수
        function showPurchaseDetail(purchaseId) {
            // AJAX로 구매 상세 내역 가져오기
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_purchase_detail.php?id=" + purchaseId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("purchaseDetailContent").innerHTML = xhr.responseText;
                    document.getElementById("purchaseDetailModal").style.display = "block";
                }
            };
            // 닫기 버튼에 이벤트 리스너 추가
            var closeBtn = document.querySelector("#purchaseDetailModal .close");
            if(closeBtn) {
                closeBtn.onclick = function() {
                    document.getElementById("purchaseDetailModal").style.display = "none";
                }
            }
            xhr.send();
            
        }
        
        // 모달 닫기 함수
        function closeModal() {
            document.getElementById("purchaseDetailModal").style.display = "none";
        }
        // 페이지 로드 시 닫기 버튼에 이벤트 리스너 추가
        window.onload = function() {
            var closeBtn = document.querySelector("#purchaseDetailModal .close");
            if(closeBtn) {
                closeBtn.onclick = function() {
                    document.getElementById("purchaseDetailModal").style.display = "none";
                }
            }
        };
        // 통계 데이터 로드 함수
        // 기존 loadStatistics 함수 수정
        // 전역 변수로 차트 객체들을 저장
let ageChartInstance = null;
let genderChartInstance = null;
let popularItemsChartInstance = null;
let monthlySalesChartInstance = null; 
function loadStatistics() {
    console.log("통계 데이터 로드 시작...");
    
    fetch('get_statistics.php')
        .then(response => {
            console.log("응답 상태:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("받은 데이터:", data);
            
            // 데이터가 비어있는지 확인
            if (!data || Object.keys(data).length === 0) {
                console.error("데이터가 비어있습니다!");
                return;
            }
            
            // 차트 그리기 전에 기존 차트 파괴
            if (ageChartInstance) {
                ageChartInstance.destroy();
            }
            if (genderChartInstance) {
                genderChartInstance.destroy();
            }
            if (popularItemsChartInstance) {
                popularItemsChartInstance.destroy();
            }
            // 요약 데이터 표시 (새로 추가)
            if (data.summary) {
                document.getElementById('totalSales').innerText = data.summary.total_sales ? data.summary.total_sales.toLocaleString() + '원' : '0원';
                document.getElementById('totalPurchases').innerText = data.summary.total_purchases ? data.summary.total_purchases.toLocaleString() + '건' : '0건';
                document.getElementById('totalUsers').innerText = data.summary.total_users ? data.summary.total_users.toLocaleString() + '명' : '0명';
            } else {
                document.getElementById('totalSales').innerText = '0원';
                document.getElementById('totalPurchases').innerText = '0건';
                document.getElementById('totalUsers').innerText = '0명';
            }
            // 차트 그리기
            if (data.age_stats && data.age_stats.length > 0) {
                ageChartInstance = createAgeChart(data.age_stats);
            }
            
            if (data.gender_stats && data.gender_stats.length > 0) {
                genderChartInstance = createGenderChart(data.gender_stats);
            }
            
            if (data.popular_items && data.popular_items.length > 0) {
                popularItemsChartInstance = createPopularItemsChart(data.popular_items);
            }
             if (data.monthly_sales && data.monthly_sales.length > 0) { // 이 부분 추가
                monthlySalesChartInstance = createMonthlySalesChart(data.monthly_sales);
            }
            
            // 나이대별/성별별 인기 상품 표시
            displayAgePopularItems(data.age_popular_items);
            displayGenderPopularItems(data.gender_popular_items);
        })
        .catch(error => {
            console.error('통계 데이터 로드 오류:', error);
        });
}

        // 월별 매출 추이 차트 생성 함수 (새로 추가)
        function createMonthlySalesChart(data) {
            const ctx = document.getElementById('monthlySalesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line', // 라인 그래프
                data: {
                    labels: data.map(item => item.month),
                    datasets: [{
                        label: '월별 매출',
                        data: data.map(item => item.monthly_sales),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '매출액 (원)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: '월'
                            }
                        }
                    }
                }
            });
        }


        // 나이대별 가장 많이 구매한 상품 표시 함수 (새로 추가)
        function displayAgePopularItems(data) {
            const container = document.getElementById('agePopularItems');
            container.innerHTML = ''; // 기존 내용 초기화
            if (data && data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'popular-item';
                    div.innerHTML = `
                        <span class="popular-item-age">${item.age_group}</span>
                        <span class="popular-item-name">${item.itemname}</span>
                        <span class="popular-item-quantity">${item.total_quantity}개</span>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<p>데이터가 없습니다.</p>';
            }
        }

        // 성별별 가장 많이 구매한 상품 표시 함수 (새로 추가)
        function displayGenderPopularItems(data) {
            const container = document.getElementById('genderPopularItems');
            container.innerHTML = ''; // 기존 내용 초기화
            if (data && data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'popular-item';
                    div.innerHTML = `
                        <span class="popular-item-gender">${item.gender}</span>
                        <span class="popular-item-name">${item.itemname}</span>
                        <span class="popular-item-quantity">${item.total_quantity}개</span>
                    `;
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = '<p>데이터가 없습니다.</p>';
            }
        }


                // 나이대별 차트 생성 함수
                function createAgeChart(data) {
                var ctx = document.getElementById('ageChart').getContext('2d');
                return new Chart(ctx, {
                    // 기존 설정 유지
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.age_group),
                        datasets: [{
                            label: '구매 건수',
                            data: data.map(item => item.purchase_count || item.count || 0),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        indexAxis: 'y', // 가로 막대 차트로 변경
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: '나이대별 구매 통계'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: { // X축 (나이대 라벨) 설정
                                ticks: {
                                    font: {
                                        size: 11 // X축 라벨 폰트 크기 줄이기
                                    },
                                    maxRotation: 0, // 라벨 회전 없애기
                                    minRotation: 0  // 라벨 회전 없애기
                                }
                            }
                        }
                    }
                });
            }


                // 성별 차트 생성 함수
                function createGenderChart(data) {
                    console.log("성별 데이터:", data); // 데이터 구조 확인용
                    
                    var ctx = document.getElementById('genderChart').getContext('2d');
                    return new Chart(ctx, {
                        type: 'bar', // 'pie'에서 'bar'로 변경
                        data: {
                            labels: data.map(item => item.gender),
                            datasets: [{
                                label: '구매 건수',
                                data: data.map(item => item.purchase_count || item.count || 0),
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.7)', // 남성 (파랑)
                                    'rgba(255, 99, 132, 0.7)'  // 여성 (빨강)
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 99, 132, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: { // 막대 차트니까 scales 옵션 추가
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: '구매 건수'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: '성별'
                                    },
                                    ticks: {
                                    font: {
                                        size: 11 // X축 라벨 폰트 크기 줄이기
                                    },
                                    maxRotation: 0, // 라벨 회전 없애기
                                    minRotation: 0  // 라벨 회전 없애기
                                }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false, // 범례 숨기기 (막대 차트에선 필요 없음)
                                },
                                title: {
                                    display: true,
                                    text: '성별 구매 통계'
                                }
                            }
                        }
                    });
                }


                function createPopularItemsChart(data) {
                var ctx = document.getElementById('popularItemsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',  // 'horizontalBar' 대신 'bar' 사용
                    data: {
                        labels: data.map(item => item.itemname),
                        datasets: [{
                            label: '판매량',
                            data: data.map(item => item.total_quantity),
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',  // 이 옵션을 추가해서 가로 막대 차트로 변경
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            x: { // X축 (수량) 설정
                                beginAtZero: true
                            },
                            y: { // Y축 (상품명 라벨) 설정 - 가로 막대에서는 이 축이 가로 공간에 영향
                                ticks: {
                                    font: {
                                        size: 11 // Y축 라벨 폰트 크기 줄이기
                                    },
                                    maxRotation: 0, // 라벨 회전 없애기
                                    minRotation: 0  // 라벨 회전 없애기
                                },
                                grid: {
                                    display: false // Y축 그리드 라인 숨기기 (더 깔끔하게)
                                }
                            }
                        }
                    }
                });
            }
        // 월별 매출 추이 차트 생성 함수
function createMonthlySalesChart(data) {
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    return new Chart(ctx, { // return 추가
        type: 'line', // 라인 그래프
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: '월별 매출',
                data: data.map(item => item.monthly_sales),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '매출액 (원)'
                    }
                },
                x: { // X축 (월 라벨) 설정
                    title: {
                        display: true,
                        text: '월'
                    },
                    ticks: {
                        font: {
                            size: 11 // X축 라벨 폰트 크기 줄이기
                        },
                        maxRotation: 0, // 라벨 회전 없애기
                        minRotation: 0  // 라벨 회전 없애기
                    }
                }
            }
        }
    });
}


        // 탭 클릭 시 통계 데이터 로드
        document.addEventListener('DOMContentLoaded', function() {
            var statisticsTab = document.querySelector('.tab-btn[onclick="openTab(\'statistics\')"]');
            if (statisticsTab) {
                statisticsTab.addEventListener('click', function() {
                    loadStatistics();
                });
            }
        });

        // 회원 검색 함수
        function searchMembers() {
            var input = document.getElementById("memberSearch").value.toLowerCase();
            var table = document.getElementById("memberTable");
            var rows = table.getElementsByTagName("tr");
            
            for (var i = 0; i < rows.length; i++) {
                var nameCell = rows[i].getElementsByTagName("td")[1];
                var phoneCell = rows[i].getElementsByTagName("td")[2];
                                if (nameCell || phoneCell) {
                    var name = nameCell.textContent || nameCell.innerText;
                    var phone = phoneCell.textContent || phoneCell.innerText;
                    
                    if (name.toLowerCase().indexOf(input) > -1 || phone.toLowerCase().indexOf(input) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
        
        // 구매 내역 검색 함수
        function searchPurchases() {
            var input = document.getElementById("purchaseSearch").value.toLowerCase();
            var table = document.getElementById("purchaseTable");
            var rows = table.getElementsByTagName("tr");
            
            for (var i = 0; i < rows.length; i++) {
                var idCell = rows[i].getElementsByTagName("td")[1];
                var dateCell = rows[i].getElementsByTagName("td")[4];
                var nameCell = rows[i].getElementsByTagName("td")[2];
                
                if (idCell || dateCell || nameCell) {
                    var id = idCell.textContent || idCell.innerText;
                    var date = dateCell.textContent || dateCell.innerText;
                    var name = nameCell.textContent || nameCell.innerText;
                    
                    if (id.toLowerCase().indexOf(input) > -1 || 
                        date.toLowerCase().indexOf(input) > -1 ||
                        name.toLowerCase().indexOf(input) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
        
        // 모달 외부 클릭 시 닫기
        window.onclick = function(event) {
            var modal = document.getElementById("purchaseDetailModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>


