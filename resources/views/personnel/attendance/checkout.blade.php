@extends('layouts.app')

@section('title', 'Check-out WFH - SIKERJA')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">
        Check-out WFH
    </h1>

    <p class="text-secondary mb-0">
        Ambil foto dan lokasi untuk menyelesaikan WFH.
    </p>
</div>

@if ($testMode)
    <div class="alert alert-warning">
        Mode pengujian lokal aktif.
        Check-out dapat diuji di luar waktu normal.
    </div>
@endif

<div
    id="responseMessage"
    class="alert d-none"
></div>

@if ($membership->attendance?->checkout_at)
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-5 text-success mb-3">
                ✓
            </div>

            <h2 class="h4 fw-bold">
                Check-out Berhasil
            </h2>

            <p class="text-secondary mb-0">
                {{
                    $membership->attendance
                        ->checkout_at
                        ->format('d-m-Y H:i:s')
                }}
                WIB
            </p>
        </div>
    </div>
@elseif ($canCheckout)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="alert alert-light border">
                {{ $message }}
            </div>

            <form
                id="checkoutForm"
                method="POST"
                action="{{
                    route(
                        'personnel.checkout.store',
                        $membership
                    )
                }}"
            >
                @csrf

                <h2 class="h5 fw-bold mb-3">
                    1. Foto Check-out
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
                    alt="Foto check-out"
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
                    2. Lokasi GPS
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

                <button
                    type="submit"
                    id="submitButton"
                    class="btn btn-sikerja btn-lg w-100"
                    disabled
                >
                    Simpan Check-out
                </button>
            </form>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 fw-bold">
                Check-out Belum Tersedia
            </h2>

            <p class="text-secondary mb-0">
                {{ $message }}
            </p>
        </div>
    </div>
@endif

@if (
    ! $membership->attendance?->checkout_at
    && $canCheckout
)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('checkoutForm');
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('photoCanvas');
    const preview = document.getElementById('photoPreview');

    const placeholder = document.getElementById(
        'cameraPlaceholder'
    );

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

    let stream = null;
    let photoBlob = null;
    let latitude = null;
    let longitude = null;

    function showMessage(message, type = 'danger') {
        responseMessage.className =
            `alert alert-${type}`;

        responseMessage.textContent = message;
        responseMessage.classList.remove('d-none');

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function updateSubmitButton() {
        submitButton.disabled = ! (
            photoBlob
            && latitude !== null
            && longitude !== null
        );
    }

    function stopCamera() {
        if (! stream) {
            return;
        }

        stream.getTracks().forEach(function (track) {
            track.stop();
        });

        stream = null;
    }

    startCameraButton.addEventListener(
        'click',
        async function () {
            try {
                stream = await navigator
                    .mediaDevices
                    .getUserMedia({
                        video: {
                            facingMode: {
                                ideal: 'user'
                            }
                        },
                        audio: false
                    });

                video.srcObject = stream;
                video.classList.remove('d-none');
                placeholder.classList.add('d-none');
                captureButton.disabled = false;
            } catch (error) {
                showMessage(
                    'Kamera tidak dapat dibuka. Izinkan akses kamera.'
                );
            }
        }
    );

    captureButton.addEventListener(
        'click',
        function () {
            if (! stream || ! video.videoWidth) {
                showMessage('Kamera belum siap.');
                return;
            }

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

            canvas
                .getContext('2d')
                .drawImage(
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
                            'Foto gagal dibuat.'
                        );
                        return;
                    }

                    photoBlob = blob;
                    preview.src =
                        URL.createObjectURL(blob);

                    preview.classList.remove('d-none');

                    stopCamera();
                    video.classList.add('d-none');
                    placeholder.classList.remove('d-none');

                    placeholder.textContent =
                        'Foto check-out sudah diambil.';

                    captureButton.textContent =
                        'Ambil Ulang Foto';

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
            locationButton.textContent =
                'Mengambil Lokasi...';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    latitude =
                        position.coords.latitude;

                    longitude =
                        position.coords.longitude;

                    locationStatus.className =
                        'alert alert-success';

                    locationStatus.innerHTML =
                        '<strong>Lokasi berhasil diperoleh.</strong>'
                        + '<br>Latitude: '
                        + latitude.toFixed(7)
                        + '<br>Longitude: '
                        + longitude.toFixed(7)
                        + '<br>Akurasi: ±'
                        + Math.round(
                            position.coords.accuracy
                        )
                        + ' meter';

                    locationButton.disabled = false;
                    locationButton.textContent =
                        'Perbarui Lokasi';

                    updateSubmitButton();
                },

                function () {
                    locationButton.disabled = false;
                    locationButton.textContent =
                        'Ambil Lokasi';

                    showMessage(
                        'Lokasi tidak dapat diperoleh. Aktifkan GPS.'
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
                    'Foto dan lokasi wajib dilengkapi.'
                );
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent =
                'Menyimpan Check-out...';

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
                'checkout.jpg'
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
                    const errors = result.errors
                        ? Object.values(
                            result.errors
                        ).flat().join(' ')
                        : null;

                    throw new Error(
                        errors
                        || result.message
                        || 'Check-out gagal.'
                    );
                }

                showMessage(
                    result.message,
                    'success'
                );

                window.location.href =
                    result.redirect;
            } catch (error) {
                showMessage(error.message);

                submitButton.disabled = false;
                submitButton.textContent =
                    'Simpan Check-out';
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
