<?php
declare(strict_types=1);
session_start();
// Sprawdzenie logowania
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
$username = $_SESSION['username'];
$homeDir = $_SESSION['home_dir'];
// Połączenie z bazą
$conn = mysqli_connect("127.0.0.1", "dawzursz_mynetflix", "Dawidek7003$", "dawzursz_mynetflix");
if (!$conn) die("Błąd połączenia z bazą: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
// Pobranie id playlisty
$idpl = isset($_GET['idpl']) ? intval($_GET['idpl']) : 0;
// Pobranie informacji o playliście
$sqlCheck = "SELECT public, idu, name FROM playlistname WHERE idpl = ?";
$stmt = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmt, "i", $idpl);
mysqli_stmt_execute($stmt);
$resultCheck = mysqli_stmt_get_result($stmt);
$playlist = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmt);
if (!$playlist) {
    die("Playlista nie istnieje.");
}
// Sprawdzenie dostępu: publiczna = każdy zalogowany, prywatna = tylko właściciel
if ($playlist['public'] == 0 && $playlist['idu'] != $idu) {
    die("Brak dostępu do tej prywatnej playlisty.");
}
// Pobranie utworów w playliście
$sqlFilms = "
    SELECT s.title, s.director, s.filename, u.username
    FROM playlistdatabase pd
    JOIN film s ON pd.idf = s.idf
    JOIN users u ON s.idu = u.idu
    WHERE pd.idpl = ?
";
$stmt = mysqli_prepare($conn, $sqlFilms);
mysqli_stmt_bind_param($stmt, "i", $idpl);
mysqli_stmt_execute($stmt);
$resultFilms = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Odtwarzanie playlisty: <?php echo htmlspecialchars($playlist['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="twoj_css.css">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="twoj_js.js"></script> 
<style>
/* Aktywny utwór */
#playlist li.active {
    font-weight: bold;
    color: #007bff;
}
#playlist li {
    cursor: pointer;
    padding: 5px 0;
}
</style>
</head>
<body onload="myLoadHeader()">
<div id="myHeader"></div>
<main>
    <section class="sekcja1">
        <div class="container-fluid">
            <h2>Odtwarzanie playlisty: <?php echo htmlspecialchars($playlist['name']); ?></h2>
            <?php if (mysqli_num_rows($resultFilms) === 0): ?>
                <p>Playlista jest pusta.</p>
            <?php else: ?>
                <div class="video-player">
                    <!-- Wyświetlanie nazwy aktualnego utworu -->
                    <div id="currentTrackName" class="mb-2"><strong>Teraz odtwarzany:</strong> </div>
                    <video id="video" controls style="width: 400px; height: auto; max-height: 400px; object-fit: contain;">
                        Twoja przeglądarka nie obsługuje odtwarzacza video.
                    </video>
                    <h2>Pozycje zawarte w playliście</h2>
                    <ul id="playlist" class="list-group mt-3">
                        <?php while ($film = mysqli_fetch_assoc($resultFilms)): ?>
                            <?php
                            $filepath = 'films/' . $film['username'] . '/' . $film['filename'];
                            $fileExists = file_exists($filepath);
                            ?>
                            <li class="list-group-item" <?php if($fileExists) echo 'data-src="'.$filepath.'"'; ?> data-title="<?php echo htmlspecialchars($film['title']); ?>">
                                <strong><?php echo htmlspecialchars($film['title']); ?></strong>
                                <br><small><?php echo htmlspecialchars($film['director']); ?></small>
                                <?php if (!$fileExists): ?>
                                    <span class="text-danger"> - Plik video nie istnieje</span>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <script>
                var video = document.getElementById('video'); // Element video
                var playlist = document.getElementById('playlist'); // Lista utworów
                var tracks = playlist.getElementsByTagName('li'); // Wszystkie elementy li
                var currentTrack = 0; // Indeks aktualnego utworu
                var currentTrackName = document.getElementById('currentTrackName'); // Element do wyświetlania nazwy utworu
                function playTrack(trackIndex) {
                    if (!tracks[trackIndex]) return;

                    if (tracks[currentTrack]) {
                        tracks[currentTrack].classList.remove('active'); // Usuń klasę 'active' z poprzedniego
                    }
                    currentTrack = trackIndex;
                    tracks[currentTrack].classList.add('active'); // Dodaj klasę 'active' do aktualnego
                    var trackSrc = tracks[currentTrack].getAttribute('data-src');
                    var trackTitle = tracks[currentTrack].getAttribute('data-title'); // Pobranie tytułu utworu
                    if(trackSrc){
                        video.src = trackSrc;
                        video.play();
                        // Aktualizacja wyświetlanej nazwy utworu
                        currentTrackName.innerHTML = "<strong>Teraz odtwarzany:</strong> " + trackTitle;
                    }
                }
                // Automatyczne przejście do następnego utworu
                video.onended = function() {
                    if (currentTrack + 1 < tracks.length) {
                        playTrack(currentTrack + 1);
                    } else {
                        playTrack(0);
                    }
                };
                // Kliknięcie w utwór z listy
                playlist.addEventListener('click', function(e) {
                    var target = e.target;
                    while(target && target.nodeName !== 'LI') {
                        target = target.parentNode;
                    }
                    if(target && target.getAttribute('data-src')) {
                        var clickedIndex = Array.prototype.indexOf.call(tracks, target);
                        playTrack(clickedIndex);
                    }
                });
                // Start odtwarzania pierwszego utworu
                playTrack(0);
                </script>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary mt-3">Powrót</a>
        </div>
    </section>
</main>
<?php require_once 'footer.php'; ?>
</body>
</html>