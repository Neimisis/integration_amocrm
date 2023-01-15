<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <title>amoCRM</title>
  <link rel="stylesheet" href="assets/css/app.css?v=<?php echo fileatime('assets/css/app.css') ?>">
</head>
<body>
  <div class="wrapper">
    <button class="popup__open js-popup-trigger">Кликни</button>
    <div class="popup" id="popup">
      <div class="popup__container">
        <div class="popup__content">
          <div class="popup__window">
            <button class="popup__close js-popup-trigger" type="button"><img src="assets/img/popup_close.png" alt="Иконка закрыть"></button>
            <div class="popup__grid">
              <div class="popup__title">Получите набор файлов для руководителя:</div>
              <div class="popup__images">
                <img class="popup__img-files" src="assets/img/popup_files.png" alt="Иконки файлов">
                <img class="popup__img-books" src="assets/img/popup_books.png" alt="Изображение книг">
              </div>
              <form class="popup__form" action="/system/core.php" method="POST" id="popup-form">
                <div class="popup__group">
                  <label class="popup__label" for="">Введите Email для получения файлов:</label>
                  <input class="popup__control" type="email" name="email" placeholder="E-mail">
                </div>
                <div class="popup__group">
                  <label class="popup__label" for="">Введите телефон для подтверждения доступа:</label>
                  <input class="popup__control" type="tel" name="phone" placeholder="+7 (000) 000-00-00">
                </div>
                <div class="popup__actions">
                  <button class="popup__button button3d" type="button" id="popup-submit">
                    <span class="button3d__front">
                      <span>Скачать файлы</span><img src="assets/img/popup_hand.png" alt="Иконка руки">
                    </span>
                    <span class="button3d__back">
                    </span>
                  </button>
                </div>
                <div class="popup__sizes"><span>PDF 4,7 MB</span><span>DOC 0,8 MB</span><span>XLS 1,2 MB</span></div>
              </form>
            </div>
          </div>
        </div>  
      </div>
      <div class="popup__overlay"></div>
    </div>
  </div>
  <script src="assets/js/app.js?v=<?php echo fileatime('assets/js/app.js') ?>"></script>
</body>
</html>