<?php
if (isset($_POST['submit'])) {
    $err = array();
    try {

        $login = trim($_POST['login']);
        $password = md5(md5(trim($_POST['password'])));
        $email = trim($_POST['email']);
        $first_name = trim($_POST['first_name']);
        $sur_name = trim($_POST['sur_name']);
        $pater_name = trim($_POST['pater_name']);

        if (empty($login) or empty($password) or empty($email) or empty($first_name) or empty($sur_name)) //если пользователь не ввел логин или пароль, то выдаем ошибку и останавливаем скрипт
        {
            exit("Вы ввели не всю информацию, вернитесь назад и заполните все поля!");
        }

        //если логин и пароль введены, то обрабатываем их

        # проверям логин
        if (!preg_match("/^[a-zA-Z0-9]+$/", $login)) {
            //throw new Exception("Логин может состоять только из букв английского алфавита и цифр");
            $err[] = "Логин может состоять только из букв английского алфавита и цифр";
        }
        if (strlen($login) < 3 or strlen($login) > 30) {
            //throw new Exception("Логин должен быть не меньше 3-х символов и не больше 30");
            $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
        }
        //проверяем электронную почту
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //throw new Exception("Логин должен быть не меньше 3-х символов и не больше 30");
            $err[] = "Адрес электрнонной почты указан некорректно";
        }

        print_r($_FILES);

        //echo "fupload:" . $_FILES['fupload'] . "<br>";

        if (!empty($_FILES['fupload'])) //проверяем, отправил    ли пользователь изображение
        {
            $fupload = $_FILES['fupload'];
        }


        if (!isset($fupload) or empty($fupload) or $fupload == '' or $fupload['size'] == 0) {
            //если переменной не существует (пользователь не отправил    изображение),то присваиваем ему заранее приготовленную картинку с надписью    "нет аватара"
            $avatar    = "avatars/net-avatara.jpg";
        } else {

            //иначе - загружаем изображение пользователя
            $path_to_90_directory = 'avatars/'; //папка,  куда будет загружаться начальная картинка и ее сжатая копия          
            $types = array('image/png', 'image/png', 'image/jpeg');
            $size = 1024000;

            //проверка формата исходного изображения
            if (!in_array($_FILES['fupload']['type'], $types))
                die('Прикрепляемый файл должен быть файлом изображения. <a href="?">Попробовать другой файл?</a>');
            else {
                $filename =    $_FILES['fupload']['name'];
                $source =    $_FILES['fupload']['tmp_name'];
                $target =    $path_to_90_directory . $filename;
                echo  $filename . "<br>";
                echo   $source . "<br>";
                echo  $target . "<br>";
                move_uploaded_file($source,    $target); //загрузка оригинала в папку $path_to_90_directory           

                if (preg_match('/[.](PNG)|(png)$/',    $filename)) {
                    $im =    imagecreatefrompng($path_to_90_directory . $filename); //если    оригинал был в формате png, то создаем изображение в этом же формате.    Необходимо для последующего сжатия
                }

                if (preg_match('/[.](JPG)|(jpg)|(jpeg)|(JPEG)$/',    $filename)) {
                    $im =    imagecreatefromjpeg($path_to_90_directory . $filename); //если оригинал был в формате jpg, то создаем изображение в этом же    формате. Необходимо для последующего сжатия
                }
                // Создание квадрата 90x90
                // dest - результирующее изображение 
                // w - ширина изображения 
                // ratio - коэффициент пропорциональности           
                $w    = 90;  //    квадратная 90x90. Можно поставить и другой размер.          
                // создаём исходное изображение на основе 
                // исходного файла и определяем его размеры 
                $w_src    = imagesx($im); //вычисляем ширину
                $h_src    = imagesy($im); //вычисляем высоту изображения
                // создаём    пустую квадратную картинку 
                // важно именно    truecolor!, иначе будем иметь 8-битный результат 
                $dest = imagecreatetruecolor($w, $w);
                //    вырезаем квадратную серединку по x, если фото горизонтальное 
                if ($w_src > $h_src)
                    imagecopyresampled(
                        $dest,
                        $im,
                        0,
                        0,
                        round((max($w_src, $h_src) - min($w_src, $h_src)) / 2),
                        0,
                        $w,
                        $w,
                        min($w_src, $h_src),
                        min($w_src, $h_src)
                    );
                // вырезаем    квадратную верхушку по y, 
                // если фото    вертикальное (хотя можно тоже серединку) 
                if ($w_src < $h_src)
                    imagecopyresampled(
                        $dest,
                        $im,
                        0,
                        0,
                        0,
                        0,
                        $w,
                        $w,
                        min($w_src, $h_src),
                        min($w_src, $h_src)
                    );
                // квадратная картинка    масштабируется без вырезок 
                if ($w_src == $h_src)
                    imagecopyresampled($dest,    $im, 0, 0, 0, 0, $w, $w, $w_src, $w_src);
                $date = time();    //вычисляем время в настоящий момент.
                imagejpeg($dest,    $path_to_90_directory . $date . ".jpg"); //сохраняем    изображение формата jpg в нужную папку, именем будет текущее время. Сделано,    чтобы у аватаров не было одинаковых имен.          
                //почему именно jpg? Он занимает очень мало места + уничтожается    анимирование gif изображения, которое отвлекает пользователя. Не очень    приятно читать его комментарий, когда краем глаза замечаешь какое-то    движение.          
                $avatar    = $path_to_90_directory . $date . ".jpg"; //заносим в переменную путь до аватара. 
            }
        }


        //запоминаем сведения о ФИО
        $name_poles = ", first_name, sur_name";
        $name_variables = ", '" . $first_name . "','" . $sur_name . "'";
        if (!empty($pater_name)) {
            $name_poles =    $name_poles . ", pater_name";
            $name_variables =   $name_variables . ", '" . $pater_name . "'";
        }
        // подключаемся к базе
        include("bd.php");
        mysqli_report(MYSQLI_REPORT_ALL);
        # проверяем, не существует ли пользователя с таким именем


        $querry = "SELECT COUNT(*) FROM users WHERE user_login='" . mysqli_real_escape_string($db, $login) . "'";

        if (mysqli_real_query($db, $querry) === false) {
            throw new Exception("Неизвестная ошибка базы");
        } else {
            $result = mysqli_use_result($db);
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            if ($row[0] != 0)
                //throw new Exception("Пользователь с таким логином уже существует в базе данных");
                $err[] = "Пользователь с таким логином уже существует в базе данных";
        }
        if (count($err) == 0) {
            $q = "INSERT INTO users (user_login, user_password, email, avatar" . $name_poles . ")    VALUES('$login','$password','$email','$avatar'" . $name_variables . ")";
            echo $q;
            if (mysqli_real_query($db, $q)) {
                header("Location: reg_message.php");
                exit();
            } else
                throw new Exception("Не удалось добавить пользователя");
            //"Не удалось добавить пользователя";
        } else {
            echo "<b>При регистрации произошли следующие ошибки:</b><br>";
            //print_r($err);
            foreach ($err as $error) {
                echo $error . "<br>";
            }
        }
    } catch (Exception $e) {
        echo "<b>Исключительная ситуация:</b><br>";
        echo  $e->getMessage() . "<br>";
    }
}