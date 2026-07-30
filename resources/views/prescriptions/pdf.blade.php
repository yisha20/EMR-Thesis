<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin:34px 42px; }
        body { color:#123b56; font-family:DejaVu Sans,sans-serif; font-size:12px; }
        .prescription-document { padding:0; }
        .prescription-header { border-bottom:2px solid #2f78a0; min-height:76px; padding-bottom:14px; text-align:center; }
        .prescription-logo { float:left; width:76px; }
        .prescription-logo img { display:block; height:auto; max-height:64px; width:64px; }
        .prescription-logo strong { color:#2f78a0; display:block; font-size:12px; margin-top:25px; }
        .prescription-header h1 { font-size:22px; margin:5px 0; }
        .prescription-header span,.prescription-meta span { color:#627f91; font-size:10px; }
        .prescription-meta { border-bottom:1px solid #b7d9ee; padding:18px 0; }
        .prescription-meta div { display:inline-block; margin-bottom:10px; width:49%; }
        .prescription-meta span,.prescription-meta strong { display:block; }
        .prescription-rx { min-height:300px; padding:22px 0; }
        .rx-symbol { color:#2f78a0; font-family:serif; font-size:42px; font-style:italic; font-weight:bold; }
        .prescription-type { color:#627f91; font-size:10px; font-weight:bold; text-transform:uppercase; }
        .prescribed-medication { border:1px solid #d9e8f0; border-radius:6px; margin:10px 0; padding:11px; }
        .prescribed-medication h2 { font-size:16px; margin:0 0 5px; }
        .prescribed-medication dl { margin:0; }
        .prescribed-medication div { display:inline-block; margin-bottom:7px; vertical-align:top; width:49%; }
        .prescribed-medication dt { color:#627f91; font-size:9px; font-weight:bold; text-transform:uppercase; }
        .prescribed-medication dd { margin:2px 0 0; }
        .prescribed-medication p,.prescription-instructions p { line-height:1.5; margin:0; }
        .prescription-instructions { border-top:1px solid #b7d9ee; padding-top:16px; }
        .prescription-instructions h2 { font-size:12px; text-transform:uppercase; }
        .prescription-signature { margin-left:430px; margin-top:70px; text-align:center; width:240px; }
        .signature-line { border-top:1px solid #123b56; margin-bottom:7px; }
        .prescription-signature span { color:#627f91; display:block; font-size:10px; }
    </style>
</head>
<body>@include('prescriptions.document')</body>
</html>
