<script type="text/javascript">
    $(document).on('click', '#copy', async function() {
        var text = $(this).data("text");
        try {
            await navigator.clipboard.writeText(text);
            toastr.success('Copied!');
        } catch (err) {
            console.error('Failed to copy text:', err);
        }
    });

    $(document).on('click', '.delivery_message', function() {
        if ($(this).data('message') !== "") {
            $("#show_message").text($(this).data('message'));
        } else {
            $("#show_message").text('No message found to show.');
        }
    });

    $(document).on('click', '.order_note', function() {
        if ($(this).data('note') !== "") {
            $("#show_note").html($(this).data('note').replace(/&lt;br&gt;/g, '<br>'));
        } else {
            $("#show_note").text('No note found to show.');
        }
    });

    $(document).ready(function() {
        $('#status').change(function() {
            var selectedstatus = $(this).val();
            window.location.href = '?status=' + selectedstatus;
        });
    });
</script>
