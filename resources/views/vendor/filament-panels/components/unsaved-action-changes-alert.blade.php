@if (filament()->hasUnsavedChangesAlerts())
    @script
        <script {!! csp_nonce_attribute() !!}>
            setUpUnsavedActionChangesAlert({
                resolveLivewireComponentUsing: () => @this,
                $wire,
            })
        </script>
    @endscript
@endif
