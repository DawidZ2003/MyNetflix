<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
$idpl = isset($_GET['idpl']) ? (int)$_GET['idpl'] : 0;
$conn = mysqli_connect("127.0.0.1", "dawzursz_mynetflix", "Dawidek7003$", "dawzursz_mynetflix");
if (!$conn) die("Błąd połączenia: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
// Pobranie listy utworów użytkownika oraz utworów z publicznych playlist innych użytkowników
$sql = "
SELECT s.idf, s.title,
       CASE WHEN s.idu = ? THEN 'Twoje' ELSE 'Publiczna' END AS source
FROM film s
WHERE (s.idu = ? OR s.idf IN (
          SELECT pd.idf
          FROM playlistdatabase pd
          JOIN playlistname p ON pd.idpl = p.idpl
          WHERE p.public = 1
      ))
AND s.idf NOT IN (
      SELECT idf FROM playlistdatabase WHERE idpl = ?
)
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $idu, $idu, $idpl);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$filmy = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
// Obsługa dodawania utworów do playlisty
if (isset($_POST['add'])) {
    $selectedIdf = $_POST['films'] ?? [];
    foreach ($selectedIdf as $idf) {
        // Dodajemy tylko jeśli jeszcze nie ma tego utworu
        $sql = "INSERT IGNORE INTO playlistdatabase (idpl, idf) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $idpl, $idf);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: index.php");
    exit();
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Dodaj filmy do playlisty</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<h2>Dodaj filmy do playlisty</h2>
<?php if (empty($filmy)): ?>
    <div class="alert alert-info">Brak dostępnych filmów do dodania.</div>
    <a href="index.php" class="btn btn-secondary btn-sm ms-2">Powrót</a>
<?php else: ?>
<form action="" method="post">
    <div class="mb-3">
        <?php foreach ($filmy as $film): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="films[]" value="<?php echo $film['idf']; ?>" id="film<?php echo $film['idf']; ?>">
                <label class="form-check-label" for="film<?php echo $film['idf']; ?>">
                    <?php echo htmlspecialchars($film['title']); ?> 
                    <small class="text-muted">(<?php echo $film['source']; ?>)</small>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" name="add" class="btn btn-primary btn-sm">Dodaj do playlisty</button>
    <a href="index.php" class="btn btn-secondary btn-sm ms-2">Powrót</a>
</form>
<?php endif; ?>
</body>
</html>
