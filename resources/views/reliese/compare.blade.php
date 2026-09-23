<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>
    /*
    |--------------------------------------------------------------------------
    | Auto Hide Bootstrap Alerts
    |--------------------------------------------------------------------------
    */

    setTimeout(function() {

        document
            .querySelectorAll('.alert')
            .forEach(function(alert) {

                const bsAlert =
                    bootstrap.Alert.getOrCreateInstance(alert);

                bsAlert.close();

            });

    }, 5000);


    /*
    |--------------------------------------------------------------------------
    | Confirm Model Regeneration
    |--------------------------------------------------------------------------
    |
    | Do not use route('reliese.generate') here.
    | We simply detect forms containing a table input.
    |
    */

    document
        .querySelectorAll('form')
        .forEach(function(form) {

            const tableInput =
                form.querySelector('input[name="table"]');

            const csrfToken =
                form.querySelector('input[name="_token"]');

            if (!tableInput || !csrfToken) {
                return;
            }

            form.addEventListener('submit', function(event) {

                const tableName =
                    tableInput.value;

                if (!tableName) {
                    return;
                }

                const confirmed = confirm(
                    'Regenerate the Reliese model for "' +
                    tableName +
                    '"?'
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            });

        });
</script>

</body>

</html>