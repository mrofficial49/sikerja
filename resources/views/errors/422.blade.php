@include('errors._layout', [
    'code' => '422',
    'title' => 'Permintaan Tidak Dapat Diproses',

    'message' =>
        'Data atau tindakan yang dikirim belum memenuhi ketentuan sistem.',

    'hint' =>
        'Periksa kembali isian formulir dan status pekerjaan sebelum mengulang proses.',
])
