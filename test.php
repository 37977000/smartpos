<?php
session_start();
$_SESSION['test'] = 'Hello World';
echo session_save_path().'<br>';
echo file_get_contents(session_save_path().'/sess_'.session_id());