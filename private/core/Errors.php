<?php

class Errors
{
    static public function error($code)
    {
        $errors = [
            403 => ['Access Denied', 'Try another way'],
            404 => ['Page Not Found', 'Try another Route'],
            500 => ['Server Error', 'Try Later'],
        ];

        if (!isset($errors[$code])) {
            $code = 500;
        }

        http_response_code($code);

        [$message, $title] = $errors[$code];

        ?>

        <!DOCTYPE html>
        <html lang="fa" dir="rtl">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title><?= $code ?> | <?= $message ?></title>

            <style>
                *{box-sizing:border-box;margin:0;padding:0}html,body{width:100%;height:100%}body{background:#050505;color:#fff;font-family:Tahoma,Arial,sans-serif;display:grid;place-items:center;overflow:hidden}.grid{position:fixed;inset:0;background-image:linear-gradient(#ffffff08 1px,transparent 1px),linear-gradient(90deg,#ffffff08 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(circle at center,#000 10%,transparent 75%);pointer-events:none}.glow{position:fixed;width:450px;height:450px;border-radius:50%;background:#00e599;filter:blur(160px);opacity:.055;pointer-events:none}.error{position:relative;text-align:center;padding:40px;z-index:2}.code{font:900 150px/.8 Arial,sans-serif;letter-spacing:-12px;color:#fff;text-shadow:0 0 50px #ffffff12;position:relative}.code:after{content:'';position:absolute;bottom:-18px;left:50%;width:70px;height:2px;background:#00e599;box-shadow:0 0 18px #00e599;transform:translateX(-50%)}h1{font-size:22px;font-weight:500;margin-top:45px;color:#fff}.english{direction:ltr;font:500 11px 'Fira Code',monospace;color:#555;margin-top:10px;letter-spacing:.08em}@media(max-width:600px){.error{padding:25px}.code{font-size:100px;letter-spacing:-8px}h1{font-size:19px}}
            </style>
        </head>

        <body>

            <div class="grid"></div>
            <div class="glow"></div>

            <main class="error">
                <div class="code"><?= $code ?></div>
                <h1><?= $message ?></h1>
                <div class="english"><?= $title ?></div>
            </main>

        </body>

        </html>

    <?php

    exit;
}

}
