@if (session('success'))
    <script>
        $(document).ready(function() {
            toastr.success(@json(session('success')))
        });
    </script>
@endif

@if (session('error'))
    <script>
        $(document).ready(function() {
            toastr.error(@json(session('error')))
        });
    </script>
@endif


@if (session('info'))
    <script>
        $(document).ready(function() {
            toastr.info(@json(session('info')))
        });
    </script>
@endif
