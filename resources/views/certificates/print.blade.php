<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Print {{ $certificate->certificate_number }}</title></head>
<body style="margin:0;background:#fff;">
    @include('certificates.partials.certificate-content')
    <script>
        window.addEventListener('load', function () {
            var ready = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
            ready.then(function () { window.setTimeout(function () { window.print(); }, 250); });
        });
    </script>
</body>
</html>
