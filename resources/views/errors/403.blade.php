@include('errors._layout', [
    'code' => '403',
    'title' => 'Akses Ditolak',

    'message' =>
        'Anda tidak memiliki hak akses untuk membuka halaman ini.',

    'hint' =>
        'Gunakan menu yang tersedia sesuai dengan role dan kewenangan akun Anda.',
])
