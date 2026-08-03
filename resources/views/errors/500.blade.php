@include('errors._layout', [
    'code' => '500',
    'title' => 'Terjadi Gangguan Sistem',

    'message' =>
        'Sistem mengalami kendala saat memproses permintaan Anda.',

    'hint' =>
        'Tidak perlu mengirimkan data berulang kali. Coba kembali beberapa saat lagi.',
])
