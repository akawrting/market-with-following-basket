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
            xhr.send();
        }
        
        // 모달 닫기 함수
        function closeModal() {
            document.getElementById("purchaseDetailModal").style.display = "none";
        }
        
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


