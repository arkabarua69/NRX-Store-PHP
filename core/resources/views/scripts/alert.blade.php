@if (session('success'))
    <script>
        $(document).ready(function() {
            toastr.success('{{ session('success') }}')
        });
    </script>
@endif

@if (session('error'))
    <script>
        $(document).ready(function() {
            toastr.error('{{ session('error') }}')
        });
    </script>
@endif


@if (session('info'))
    <script>
        $(document).ready(function() {
            toastr.info('{{ session('info') }}')
        });
    </script>
@endif
