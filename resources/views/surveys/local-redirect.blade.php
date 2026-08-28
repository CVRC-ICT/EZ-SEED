<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resuming your survey&hellip;</title>
</head>
<body>
    <p style="font-family: sans-serif; text-align: center; margin-top: 4rem; color: #64748B;">
        Resuming your survey&hellip;
    </p>
    <script src="{{ asset('js/offline-sync.js') }}"></script>
    <script>
        (async function () {
            const uuid = @json($uuid);
            const survey = await EZSeedOffline.getSurvey(uuid);
            if (!survey) {
                window.location.href = '{{ route('surveys.my-surveys') }}';
                return;
            }
            if (survey.progress_status === 'completed' || survey.progress_status === 'submitted') {
                window.location.href = `/survey/local/${uuid}/review`;
            } else {
                window.location.href = `/survey/local/${uuid}/step/${survey.current_step}`;
            }
        })();
    </script>
</body>
</html>