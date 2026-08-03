@php
    /*
     * Nilai bawaan halaman error.
     */
    $code = $code ?? '500';
    $title = $title ?? 'Terjadi Gangguan';

    $message = $message
        ?? 'Sistem tidak dapat memproses permintaan Anda.';

    $hint = $hint
        ?? 'Silakan kembali ke halaman sebelumnya atau beranda.';
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta name="robots" content="noindex, nofollow">

    <title>
        {{ $code }} - {{ $title }} | SIKERJA
    </title>
</head>

<body
    style="
        margin: 0;
        min-height: 100vh;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        background: linear-gradient(135deg, #eef4f0, #dfeae3);
        font-family: Arial, Helvetica, sans-serif;
        color: #26332d;
    "
>
    <main
        style="
            width: 100%;
            max-width: 950px;
            display: flex;
            flex-wrap: wrap;
            overflow: hidden;
            border: 1px solid #d6e1da;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 28px 70px rgba(23, 59, 43, 0.18);
        "
    >
        {{-- Panel hijau --}}
        <section
            style="
                flex: 1 1 300px;
                min-height: 430px;
                padding: 42px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                box-sizing: border-box;
                color: #ffffff;
                background: linear-gradient(145deg, #173b2b, #285d43);
            "
        >
            {{-- Identitas aplikasi --}}
            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    font-size: 18px;
                    font-weight: 800;
                    letter-spacing: 2px;
                "
            >
                <span
                    style="
                        width: 42px;
                        height: 48px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        border: 2px solid #d4af37;
                        border-radius: 14px 14px 18px 18px;
                        color: #d4af37;
                    "
                >
                    S
                </span>

                <span>SIKERJA</span>
            </div>

            {{-- Kode error --}}
            <div
                style="
                    margin: 35px 0;
                    font-size: clamp(82px, 13vw, 130px);
                    font-weight: 900;
                    line-height: 0.9;
                    letter-spacing: -8px;
                    color: #ffffff;
                "
            >
                {{ $code }}
            </div>

            <div
                style="
                    max-width: 300px;
                    font-size: 14px;
                    line-height: 1.7;
                    color: rgba(255, 255, 255, 0.76);
                "
            >
                Sistem Informasi Kinerja dan Aktivitas Personel
            </div>
        </section>

        {{-- Panel informasi --}}
        <section
            style="
                flex: 1.3 1 380px;
                min-height: 430px;
                padding: 55px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                box-sizing: border-box;
                background: #ffffff;
            "
        >
            <div
                style="
                    margin-bottom: 15px;
                    color: #285d43;
                    font-size: 12px;
                    font-weight: 800;
                    letter-spacing: 3px;
                    text-transform: uppercase;
                "
            >
                Informasi Sistem
            </div>

            <h1
                style="
                    margin: 0 0 18px;
                    color: #173b2b;
                    font-size: clamp(32px, 5vw, 48px);
                    line-height: 1.15;
                "
            >
                {{ $title }}
            </h1>

            <p
                style="
                    margin: 0 0 13px;
                    color: #26332d;
                    font-size: 17px;
                    line-height: 1.7;
                "
            >
                {{ $message }}
            </p>

            <p
                style="
                    margin: 0;
                    color: #6c7872;
                    font-size: 14px;
                    line-height: 1.7;
                "
            >
                {{ $hint }}
            </p>

            {{-- Tombol navigasi --}}
            <div
                style="
                    margin-top: 32px;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 12px;
                "
            >
                <a
                    href="{{ url('/') }}"
                    style="
                        min-height: 46px;
                        padding: 12px 21px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        box-sizing: border-box;
                        border-radius: 12px;
                        color: #ffffff;
                        background: #285d43;
                        font-size: 14px;
                        font-weight: 700;
                        text-decoration: none;
                        box-shadow: 0 10px 22px rgba(40, 93, 67, 0.24);
                    "
                >
                    Kembali ke Beranda
                </a>

                <button
                    type="button"
                    onclick="history.back()"
                    style="
                        min-height: 46px;
                        padding: 12px 21px;
                        border: 1px solid #d6e1da;
                        border-radius: 12px;
                        cursor: pointer;
                        color: #173b2b;
                        background: #ffffff;
                        font-size: 14px;
                        font-weight: 700;
                    "
                >
                    Halaman Sebelumnya
                </button>
            </div>

            <div
                style="
                    margin-top: 28px;
                    padding-top: 21px;
                    border-top: 1px solid #dce5df;
                    color: #6c7872;
                    font-size: 12px;
                    line-height: 1.7;
                "
            >
                Apabila masalah terus terjadi, catat aktivitas terakhir
                yang dilakukan dan hubungi Admin SIKERJA.
            </div>
        </section>
    </main>
</body>
</html>
