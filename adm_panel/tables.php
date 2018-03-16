<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/adm_check.php';
include_once '../sys/inc/user.php';
user_access('adm_mysql', null, 'index.php?'.SID);
adm_check();
$set['title']='Залитие таблиц';
include_once '../sys/inc/thead.php';
title();
if (isset($_FILES['file'])) {
    $file = esc(stripcslashes(htmlspecialchars($_FILES['file']['name'])));
    $ras = strtolower(preg_replace('#^.*\.#i', null, $file));
    if ($ras != 'sql') {
        $err = lang('Не верный формат файла');
    }
    if (!isset($err)) {
        move_uploaded_file($_FILES['file']['tmp_name'], H . 'sys/update/' . $_FILES['file']['name']);
        // выполнение одноразовых         запросов
        $opdirtables = opendir(H . 'sys/update/');
        while ($rd = readdir($opdirtables)) {
            if (preg_match('#^\.#', $rd)) {
                continue;
            }
            if (isset($set['update'][$rd])) {
                continue;
            }

            if (preg_match('#\.sql$#i', $rd)) {
                include_once H.'sys/inc/sql_parser.php';
                $sql = SQLParser::getQueriesFromFile(H.'sys/update/'.$rd);

                for ($i = 0; $i < count($sql);$i++) {
                    try {
                        $db->query($sql[$i], array());
                    } catch (go\DB\Exceptions\Query $e) {
                        $err = 'Ошибка выполнения запросов!';
                        echo '<div class="foot">';
                        echo '<ol style="overflow-x: auto;font-family: monospace;font-size: small;">';
                        echo '<li><span style="color: #8F3504;">SQL-query: '.$e->getQuery().'</span></li>'."\n";
                        echo '<li><span style="color: red;">Error description: '.$e->getError()."</span></li>\n";
                        echo '<li>Error code: '.$e->getErrorCode().'</li>';
                        echo '</ol>';
                        echo '</div>'."\n";
                    }
                }

                $set['update'][$rd]=true;
                $save_settings=true;
            }
        }
        closedir($opdirtables);

        if (is_file(H . 'sys/update/' . $_FILES['file']['name'])) {
            unlink(H . 'sys/update/' . $_FILES['file']['name']);
        }
        if (!isset($err)) {
            $_SESSION['message'] = 'Таблицы успешно залиты';
            exit(header('Location: ?'));
        }
    }
}
if (isset($_GET['update'])) {
    // выполнение одноразовых     запросов
    $opdirtables=opendir(H.'sys/update/');
    while ($rd=readdir($opdirtables)) {
        if (preg_match('#^\.#', $rd)) {
            continue;
        }
        if (isset($set['update'][$rd])) {
            continue;
        }
        if (preg_match('#\.sql$#i', $rd)) {
            include_once H.'sys/inc/sql_parser.php';
            $sql=SQLParser::getQueriesFromFile(H.'sys/update/'.$rd);
            
			for ($i=0;$i<count($sql);$i++) {
                try {
                    $db->query($sql[$i], array());
                } catch (go\DB\Exceptions\Query $e) {
                    $err = 'Ошибка выполнения запросов!';
                    echo '<div class="foot">';
                    echo '<ol style="overflow-x: auto;font-family: monospace;font-size: small;">';
                    echo '<li><span style="color: #8F3504;">SQL-query: '.$e->getQuery().'</span></li>'."\n";
                    echo '<li><span style="color: red;">Error description: '.$e->getError()."</span></li>\n";
                    echo '<li>Error code: '.$e->getErrorCode().'</li>';
                    echo '</ol>';
                    echo '</div>'."\n";
                }
            }
            $set['update'][$rd]=true;
            $save_settings=true;
        }
    }
    closedir($opdirtables);
	
	$files = glob(H . 'sys/update/*.sql');
	if (count($files)) {
		foreach ($files as $file) {
			unlink($file);
		}
	} else {
		$err = 'Файлы не найдены';
	}
    
	if (!isset($err)) {
        msg("Таблицы успешно залиты!");
    }
}

err();
aut();
    echo "<form method='post' enctype='multipart/form-data' action='?$passgen'>
	Выгрузить:<br />
	<input name='file' type='file' accept='sql' /><br /><input value='Залить!' type='submit' />
	</form>
	<br /> Внимание! После загрузки файла и выполнения запроса, он будет автоматически удален!";
    echo "<div class='foot'>
	Если файл с таблицами уже в папке, то переходите по ссылке ниже.<br /> 
	&raquo;<a href='?update'>Залить из папки</a>
	</div>\n";
echo "<div class='foot'>\n";
echo "&laquo;<a href='mysql.php'>MySQL запросы</a><br />\n";
if (user_access('adm_panel_show')) {
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
}
echo "</div>\n";
include_once '../sys/inc/tfoot.php';
