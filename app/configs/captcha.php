<?php
return [
    'enable' => true,

    'charset' => 'abcdefghkmnprtuvwyzABCDEFGHJKLMNPQRTUVWXYZ2346789',
    'codeLength' => 6,
    // 登入保護：短期內登入失敗次數過多時，要求輸入驗證碼
    'maxAttempts' => 3, // 短期內失敗達此次數後，下一次登入需要驗證碼
    'attemptsWindow' => 60 * 15, // 「短期內」的時間範圍（秒），預設 15 分鐘
];