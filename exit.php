<?php

if (empty($_COOKIE['id']) or empty(($_COOKIE['hash']))) {
    //если не существует сессии с логином и паролем, значит на    этот файл попал невошедший пользователь. Ему тут не место. Выдаем сообщение    об ошибке, останавливаем скрипт
    exit("Доступ на эту    страницу разрешен только зарегистрированным пользователям. Если вы    зарегистрированы, то войдите на сайт под своим логином и паролем<br><a    href='index.php'>Главная    страница</a>");
}
print_r($_COOKIE);
//    уничтожаем переменные в куках
unset($_COOKIE['id']);
unset($_COOKIE['hash']);
setcookie("id", "", time() - 3600, "/");

setcookie("hash", "", time() - 3600, "/");

// отправляем пользователя на главную страницу.
//print_r($_COOKIE);

header("Location: index.php");