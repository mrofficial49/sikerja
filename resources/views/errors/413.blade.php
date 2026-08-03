<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>File Terlalu Besar - SIKERJA</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            box-sizing: border-box;
            background: #f4f7f4;
            color: #26352a;
            font-family: Arial, sans-serif;
        }

        .error-card {
            width: 100%;
            max-width: 520px;
            padding: 36px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .error-code {
            margin-bottom: 8px;
            color: #b02a37;
            font-size: 54px;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 14px;
            font-size: 25px;
        }

        p {
            margin: 0 0 24px;
            color: #66706a;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            padding: 11px 22px;
            border-radius: 8px;
            background: #355e3b;
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="error-code">
            413
        </div>

        <h1>
            Ukuran File Terlalu Besar
        </h1>

        <p>
            File yang dikirim melebihi batas server.
            Bukti pekerjaan hanya boleh berupa PDF dengan
            ukuran maksimal 10 MB.
        </p>

        <a
            href="javascript:history.back()"
            class="button"
        >
            Kembali
        </a>
    </div>
</body>
</html>
