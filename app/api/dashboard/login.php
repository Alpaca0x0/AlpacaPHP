<?php
Inc::clas('resp');
Resp::header();

$account = strtolower(trim(Type::string($_POST['account'] ?? '')));
$password = Type::string($_POST['password'] ?? '');

$config = Inc::config('manager');
preg_match($config['account'], $account) || Resp::error('format_not_match', 'account', '帳號格式不正確');
preg_match($config['password'], $password) || Resp::error('format_not_match', 'password', '密碼格式不正確');

Inc::clas('db');
DB::connect() || Resp::error('db_cannot_connect', '無法連線至資料庫');

Inc::clas('manager');
$manager = new Manager(account: $account);
$manager->id || Resp::error('login_failed', '帳號或密碼錯誤');

$ts = time();
$ip = Type::string(trim($_SERVER['REMOTE_ADDR'] ?? ''));

// 短期內登入失敗次數過多時，要求輸入驗證碼
$captchaConfig = Inc::config('captcha');
if($captchaConfig['enable']){
    DB::query('SELECT COUNT(`id`) AS `count` FROM `manager_events`
        WHERE `manager` = :manager AND `commit` = :commit
        AND `datetime` > DATE_SUB(FROM_UNIXTIME(:ts), INTERVAL :seconds SECOND)
    ;')::execute([
        ':manager' => $manager->id,
        ':commit' => 'login_failed',
        ':ts' => $ts,
        ':seconds' => $config['login']['attemptsWindow'],
    ]);
    !DB::error() || Resp::error('sql_query', '查詢登入紀錄時發生錯誤');
    $row = DB::fetch();
    $failedCount = Type::int($row['count'] ?? 0, 0);

    if($failedCount >= $config['login']['maxAttempts']){
        $captcha = Type::string($_POST['captcha'] ?? '', '');
        $captcha !== '' || Resp::warning('needs_captcha', '登入失敗次數過多，請輸入驗證碼');
        Inc::clas('captcha');
        Captcha::check($captcha) === true || Resp::warning('captcha_not_match', '驗證碼不正確');
    }
}

// 密碼錯誤：記錄失敗事件
if(!password_verify($password, $manager->password)){
    DB::query('INSERT INTO `manager_events` (`manager`, `commit`, `ip`, `datetime`) VALUES (:manager, :commit, :ip, FROM_UNIXTIME(:ts));')::execute([
        ':manager' => $manager->id,
        ':commit' => 'login_failed',
        ':ip' => $ip,
        ':ts' => $ts,
    ]);
    !DB::error() || Resp::error('db_cannot_insert', '寫入登入紀錄時發生錯誤');
    Resp::error('login_failed', '帳號或密碼錯誤');
}

// 登入成功：產生 token，寫入 manager_events，並存入 session
$token = bin2hex(random_bytes(32));
$expireTimestamp = $ts + $config['login']['timeout'];

DB::query('INSERT INTO `manager_events` (`manager`, `commit`, `token`, `ip`, `expire`, `datetime`)
    VALUES (:manager, :commit, :token, :ip, FROM_UNIXTIME(:expire), FROM_UNIXTIME(:ts));
')::execute([
    ':manager' => $manager->id,
    ':commit' => 'login',
    ':token' => $token,
    ':ip' => $ip,
    ':expire' => $expireTimestamp,
    ':ts' => $ts,
]);
!DB::error() || Resp::error('db_cannot_insert', '寫入登入紀錄時發生錯誤');

session_regenerate_id(true);
$_SESSION['manager'] = [
    'id' => $manager->id,
    'account' => $manager->account,
    'name' => $manager->name,
    'token' => $token,
];

Resp::success('success', $_SESSION['manager'], '登入成功');
