<?php
session_start();
// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">  
    <title>Dodaj nowy film</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="twoj_css.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="twoj_js.js"></script> 
    <style>
        /* Zmniejszenie wysokości i szerokości pól formularza */
        .form-control, .form-select {
            height: calc(1.5em + .25rem + 6px); /* połowa standardowej wysokości */
            padding: .25rem .5rem;
            font-size: 0.9rem;
            width: 50% !important; /* skrócenie szerokości do połowy kontenera */
        }
        textarea.form-control {
            height: auto; /* wysokość zależna od liczby wierszy */
            width: 50% !important;
        }
        button.btn {
            padding: .25rem .5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body onload="myLoadHeader()">
    <div id="myHeader"></div>
    <main>
        <section class="sekcja1">
            <div class="container-fluid mt-4">
                <h2>Prześlij nowy plik video</h2>
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="idu" value="<?php echo $idu; ?>">
                    <div class="mb-2">
                        <label class="form-label">Tytuł filmu:</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Reżyser:</label>
                        <input type="text" name="director" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Napisy (opcjonalnie):</label>
                        <textarea name="subtitles" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Gatunek muzyczny:</label>
                        <select name="idft" class="form-select form-select-sm" required>
                            <option value="1">dokument</option>
                            <option value="2">reportaż</option>
                            <option value="3">publicystyka</option>
                            <option value="4">film akcji</option>
                            <option value="5">sci-fi</option>
                            <option value="6">horror</option>
                            <option value="7">familijny</option>
                            <option value="8">przyrodniczy</option>
                            <option value="9">koncert</option>
                            <option value="10">animowany</option>
                            <option value="11">inny</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Wybierz plik video:</label>
                        <input type="file" name="fileToUpload" class="form-control form-control-sm" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary btn-sm">Prześlij film</button>
                </form>
            </div>
        </section>
    </main>
    <?php require_once 'footer.php'; ?>
</body>
</html>