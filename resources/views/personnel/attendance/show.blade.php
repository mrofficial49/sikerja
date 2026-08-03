@extends('layouts.app')

@section('title', 'Check-in WFH - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Check-in WFH
    </h1>

    <p class="text-secondary mb-0">
        Ambil foto langsung dan izinkan akses lokasi perangkat.
    </p>
</div>

@if ($testMode)
    <div class="alert alert-warning">
        <strong>Mode pengujian lokal aktif.</strong>
        Aturan tanggal dan jam check-in sementara dilewati.
    </div>
@endif

<div
    id="responseMessage"
    class="alert d-none"
    role="alert"
></div>

@if (! $member)
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 fw-bold">
                Tidak Ada Jadwal Aktif
            </h2>

            <p class="text-secondary mb-0">
                Anda tidak memiliki jadwal WFH aktif.
            </p>
        </div>
    </div>
@else
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-4">
                        Informasi Jadwal
                    </h2>

                    <div class="mb-3">
                        <small class="text-secondary">
                            Tanggal WFH
                        </small>

                        <div class="fw-semibold">
                            {{
                                $member->schedule->wfh_date
                                    ->translatedFormat('l, d F Y')
                            }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary">
                            Status Keikutsertaan
                        </small>

                        <div class="fw-semibold">
                            {{
                                $member->is_schedule_change
                                    ? 'Perubahan Jadwal'
                                    : 'Dijadwalkan'
                            }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary">
                            Batas Check-in
                        </small>

                        <div class="fw-semibold">
                            {{
                                $member->checkin_deadline
                                    ? $member->checkin_deadline
                                        ->format('d-m-Y H:i')
                                        . ' WIB'
                                    : '-'
                            }}
                        </div>
                    </div>

                    <div class="alert alert-light border mb-0">
                        {{ $message }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if ($member->attendance?->checkin_at)
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="display-6 mb-3">✓</div>

                        <h2 class="h4 fw-bold text-success">
                            Check-in Berhasil
                        </h2>

                        <p class="text-secondary mb-0">
                            Anda check-in pada
                            {{
                                $member->attendance
                                    ->checkin_at
                                    ->format('d-m-Y H:i:s')
                            }}
                            WIB.
                        </p>
                    </div>
                </div>
            @elseif ($canCheckIn)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form
                            id="checkinForm"
                            action="{{
                                route(
                                    'personnel.attendance.checkin',
                                    $member
                                )
                            }}"
                            method="POST"
                        >
                            @csrf

                            <h2 class="h5 fw-bold mb-3">
                                1. Ambil Foto
                            </h2>

                            <div
                                class="bg-dark rounded overflow-hidden
                                       text-center mb-3"
                            >
                                <video
                                    id="cameraVideo"
                                    class="w-100 d-none"
                                    autoplay
                                    playsinline
                                    muted
                                    style="max-height: 420px;"
                                ></video>

                                <div
                                    id="cameraPlaceholder"
                                    class="text-white-50 py-5"
                                >
                                    Kamera belum diaktifkan.
                                </div>
                            </div>

                            <canvas
                                id="photoCanvas"
                                class="d-none"
                            ></canvas>

                            <img
                                id="photoPreview"
                                class="img-fluid rounded border
                                       mb-3 d-none"
                                alt="Pratinjau foto check-in"
                            >

                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <button
                                    type="button"
                                    id="startCameraButton"
                                    class="btn btn-outline-primary"
                                >
                                    Aktifkan Kamera
                                </button>

                                <button
                                    type="button"
                                    id="captureButton"
                                    class="btn btn-primary"
                                    disabled
                                >
                                    Ambil Foto
                                </button>
                            </div>

                            <hr>

                            <h2 class="h5 fw-bold mb-3">
                                2. Ambil Lokasi GPS
                            </h2>

                            <button
                                type="button"
                                id="locationButton"
                                class="btn btn-outline-success mb-3"
                            >
                                Ambil Lokasi
                            </button>

                            <div
                                id="locationStatus"
                                class="alert alert-light border"
                            >
                                Lokasi belum diperoleh.
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label
                                    for="late_reason"
                                    class="form-label fw-semibold"
                                >
                                    Alasan Keterlambatan
                                </label>

                                <textarea
                                    id="late_reason"
                                    name="late_reason"
                                    rows="3"
                                    maxlength="1000"
                                    class="form-control"
                                    placeholder="Wajib jika check-in setelah pukul 07.10 WIB"
                                ></textarea>

                                @if ($requiresLateReason)
                                    <div class="form-text text-danger">
                                        Alasan keterlambatan wajib diisi.
                                    </div>
                                @endif
                            </div>

                            <button
                                type="submit"
                                id="submitButton"
                                class="btn btn-sikerja btn-lg w-100"
                                disabled
                            >
                                Simpan Check-in
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <h2 class="h5 fw-bold">
                            Check-in Belum Tersedia
                        </h2>

                        <p class="text-secondary mb-0">
                            {{ $message }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

@if (
    $member
    && ! $member->attendance?->checkin_at
    && $canCheckIn
)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('checkinForm');
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('photoCanvas');
    const preview = document.getElementById('photoPreview');
    const placeholder = document.getElementById('cameraPlaceholder');

    const startCameraButton = document.getElementById(
        'startCameraButton'
    );

    const captureButton = document.getElementById(
        'captureButton'
    );

    const locationButton = document.getElementById(
        'locationButton'
    );

    const locationStatus = document.getElementById(
        'locationStatus'
    );

    const submitButton = document.getElementById(
        'submitButton'
    );

    const responseMessage = document.getElementById(
        'responseMessage'
    );

    let cameraStream = null;
    let photoBlob = null;
    let latitude = null;
    let longitude = null;

    /**
     * Menampilkan pesan pada halaman.
     */
    function showMessage(message, type = 'danger') {
        responseMessage.className = `alert alert-${type}`;
        responseMessage.textContent = message;
        responseMessage.classList.remove('d-none');

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    /**
     * Tombol submit hanya aktif setelah foto
     * dan koordinat GPS tersedia.
     */
    function updateSubmitButton() {
        submitButton.disabled = ! (
            photoBlob
            && latitude !== null
            && longitude !== null
        );
    }

    /**
     * Menghentikan kamera agar baterai dan kamera
     * perangkat tidak terus digunakan.
     */
    function stopCamera() {
        if (! cameraStream) {
            return;
        }

        cameraStream
            .getTracks()
            .forEach(function (track) {
                track.stop();
            });

        cameraStream = null;
    }

    startCameraButton.addEventListener(
        'click',
        async function () {
            try {
                if (
                    ! navigator.mediaDevices
                    || ! navigator.mediaDevices.getUserMedia
                ) {
                    throw new Error(
                        'Browser tidak mendukung akses kamera.'
                    );
                }

                stopCamera();

                cameraStream = await navigator
                    .mediaDevices
                    .getUserMedia({
                        video: {
                            facingMode: {
                                ideal: 'user'
                            },

                            width: {
                                ideal: 1280
                            },

                            height: {
                                ideal: 720
                            }
                        },

                        audio: false
                    });

                video.srcObject = cameraStream;
                video.classList.remove('d-none');
                placeholder.classList.add('d-none');
                captureButton.disabled = false;

                startCameraButton.textContent =
                    'Aktifkan Ulang Kamera';
            } catch (error) {
                showMessage(
                    'Kamera tidak dapat dibuka. Izinkan akses kamera pada browser.'
                );
            }
        }
    );

    captureButton.addEventListener(
        'click',
        function () {
            if (! cameraStream || ! video.videoWidth) {
                showMessage(
                    'Kamera belum siap. Coba aktifkan kembali kamera.'
                );

                return;
            }

            /*
             * Foto diperkecil agar ukuran upload
             * tidak terlalu besar.
             */
            const maximumWidth = 900;

            const width = Math.min(
                video.videoWidth,
                maximumWidth
            );

            const height = Math.round(
                width
                * video.videoHeight
                / video.videoWidth
            );

            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d');

            context.drawImage(
                video,
                0,
                0,
                width,
                height
            );

            canvas.toBlob(
                function (blob) {
                    if (! blob) {
                        showMessage(
                            'Foto gagal dibuat. Silakan ulangi.'
                        );

                        return;
                    }

                    photoBlob = blob;

                    preview.src = URL.createObjectURL(blob);
                    preview.classList.remove('d-none');

                    captureButton.textContent =
                        'Ambil Ulang Foto';

                    stopCamera();
                    video.classList.add('d-none');
                    placeholder.classList.remove('d-none');

                    placeholder.textContent =
                        'Foto sudah berhasil diambil.';

                    updateSubmitButton();
                },
                'image/jpeg',
                0.82
            );
        }
    );

    locationButton.addEventListener(
        'click',
        function () {
            if (! navigator.geolocation) {
                showMessage(
                    'Browser tidak mendukung GPS.'
                );

                return;
            }

            locationButton.disabled = true;
            locationButton.textContent = 'Mengambil Lokasi...';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;

                    locationStatus.className =
                        'alert alert-success';

                    locationStatus.innerHTML =
                        '<strong>Lokasi berhasil diperoleh.</strong>'
                        + '<br>Latitude: '
                        + latitude.toFixed(7)
                        + '<br>Longitude: '
                        + longitude.toFixed(7)
                        + '<br>Akurasi: ±'
                        + Math.round(position.coords.accuracy)
                        + ' meter';

                    locationButton.textContent =
                        'Perbarui Lokasi';

                    locationButton.disabled = false;

                    updateSubmitButton();
                },

                function () {
                    locationButton.disabled = false;

                    locationButton.textContent =
                        'Ambil Lokasi';

                    showMessage(
                        'Lokasi tidak dapat diperoleh. Aktifkan GPS dan izinkan akses lokasi pada browser.'
                    );
                },

                {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 0
                }
            );
        }
    );

    form.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (
                ! photoBlob
                || latitude === null
                || longitude === null
            ) {
                showMessage(
                    'Foto dan lokasi GPS wajib dilengkapi.'
                );

                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';

            const formData = new FormData(form);

            formData.append(
                'latitude',
                latitude.toString()
            );

            formData.append(
                'longitude',
                longitude.toString()
            );

            formData.append(
                'photo',
                photoBlob,
                'checkin.jpg'
            );

            try {
                const response = await fetch(
                    form.action,
                    {
                        method: 'POST',

                        headers: {
                            'Accept': 'application/json'
                        },

                        body: formData
                    }
                );

                const result = await response.json();

                if (! response.ok) {
                    const validationErrors =
                        result.errors
                            ? Object.values(result.errors)
                                .flat()
                                .join(' ')
                            : null;

                    throw new Error(
                        validationErrors
                        || result.message
                        || 'Check-in gagal disimpan.'
                    );
                }

                showMessage(
                    result.message,
                    'success'
                );

                window.location.href = result.redirect;
            } catch (error) {
                showMessage(error.message);

                submitButton.disabled = false;
                submitButton.textContent =
                    'Simpan Check-in';
            }
        }
    );

    window.addEventListener(
        'beforeunload',
        stopCamera
    );
});
</script>
@endif
@endsection
