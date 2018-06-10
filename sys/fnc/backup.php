<?php

if (!isset($hard_process)) {
    $backups = go\DB\query('SELECT `time` FROM `cron` WHERE `id`="backup_mysql"')->assoc();
    if (!count($backups)) {
        go\DB\query('INSERT INTO `cron` (`id`, `time`) VALUES (?, ?i)', array('backup_mysql', time()));
        $backups = go\DB\query('SELECT `time` FROM `cron` WHERE `id`="backup_mysql"')->assoc();
    }
    foreach($backups as $backup);
    if (preg_match('#^[a-z0-9_\-\.]+\@[a-z0-9_\-\.]+$#iu', $set['mail_backup']) && ($backup['time']==null || $backup['time']<time()-60*60*24)) {
        go\DB\query('UPDATE `cron` SET `time`=?i WHERE `id`=?', array(time(), 'backup_mysql'));
        $hard_process=true;
        // Ставим ограничение на 10 минут
        if (function_exists('set_time_limit')) {
            set_time_limit(600);
        }
        if (is_file(H . 'sys/tmp/MySQL.sql.gz')) {
            unlink(H . 'sys/tmp/MySQL.sql.gz');
        }
        $list_tables=null;
        $tab = go\DB\query('SHOW TABLES FROM ' . $set['mysql_db_name'])->col();

        foreach ($tab as $table) {
            $sql = null;

            $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;' . PHP_EOL;
            $row = go\DB\query('SHOW CREATE TABLE `' . $table . '`')->row();
            $sql .= $row['Create Table'] . ';' . PHP_EOL . PHP_EOL;
            $res = go\DB\query('SELECT * FROM `' . $table . '`')->assoc();
            if (count($res)) {
                foreach ($res as $row) {
                    $keys = implode('`, `', array_keys($row));
                    $values = array_values($row);
                    foreach ($values as $k=>$v) {
                        $values[$k]=preg_replace('#(\n|\r){1,}#', '\n', $values[$k]);
                    }
                    $values2 = implode('", "', $values);
                    $values2 = '"' . $values2 . '"';
                    $values2= str_replace('""', 'NULL', $values2);

                    $sql .= 'INSERT INTO `' . $table . '` (`' . $keys . '`) VALUES (' . $values2 . ');' . PHP_EOL;
                }

                $sql .= PHP_EOL . PHP_EOL;
            }
            $fopen_mysql=fopen(H . 'sys/tmp/MySQL.sql.gz', 'a');
            if (strlen($sql)<5*1024*1024) {
                fwrite($fopen_mysql, gzencode($sql, 9));
            }
            fclose($fopen_mysql);
        }

        $EOL="\r\n";
        $subj='BackUp DCMS';
        $bound = "--".md5(uniqid(time()));

        $headers="From: \"BackUP@$_SERVER[HTTP_HOST]\" <BackUp@$_SERVER[HTTP_HOST]>$EOL";
        $headers.="To: $set[mail_backup]$EOL";
        $headers.="Subject: $subj$EOL";
        $headers.="Mime-Version: 1.0$EOL";
        $headers.="Content-Type: multipart/mixed; boundary=\"$bound\"$EOL";

        $body="--$bound$EOL";
        $body.="Content-Type: text/plain; charset=\"utf-8\"$EOL";
        $body.="Content-Transfer-Encoding: 8bit$EOL";
        $body.=$EOL;
        $body.="Автоматическая отправка BackUp базы данных";

        $body.="$EOL--$bound$EOL";

        $body.="Content-Type: application/x-gzip; name=\"MySQL.sql.gz\"$EOL";
        $body.="Content-Disposition: attachment; filename=\"MySQL.sql.gz\"$EOL";
        $body.="Content-Transfer-Encoding: Base64$EOL";
        $body.=$EOL;
        $body.=chunk_split(base64_encode(file_get_contents(H."sys/tmp/MySQL.sql.gz")));

        $body.="$EOL--$bound$EOL";
        $body.="Content-Type: text/plain; name=\"settings_6.2.dat\"$EOL";
        $body.="Content-Disposition: attachment; filename=\"settings_6.2.dat\"$EOL";
        $body.="Content-Transfer-Encoding: Base64$EOL";
        $body.=$EOL;
        $body.=chunk_split(base64_encode(file_get_contents(H."sys/dat/settings_6.2.dat")));

        $body.="$EOL--$bound--$EOL";

        mail("$set[mail_backup]", '=?utf-8?B?'.base64_encode($subj).'?=', $body, $headers);
        unlink(H."sys/tmp/MySQL.sql.gz");
    }
}
