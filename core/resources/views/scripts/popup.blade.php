<script>
    $(document).ready(function() {
        function createAndShowModal(url, image_url, content, button_text) {
            const modalHtml = `
                <div id="popup" class="modal" tabindex="-1" role="dialog" align="center">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div style="padding: 15px;" align="right">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="padding: 0px 20px 20px 20px;">
                                ${image_url ? `<img src="${image_url}" class="img-fluid" loading="eager">` : ''}
                                ${content ? `<div class="popup-text" style="padding: 15px 0px; font-weight: 400; font-size: 14px; color: var(--primary-font-color);">${content}</div>` : ''}
                                ${url ? `<a class="btn theme-btn" href="${url}">${button_text}</a>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            const modal = $(modalHtml);
            $('body').append(modal);
            modal.modal('show');
        }

        function fetchPopups() {
            const popupRoute = '{{ route('popup') }}';

            fetch(popupRoute)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error fetching popups from ${popupRoute}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const popups = data.popups;
                    popups.forEach(popup => {
                        createAndShowModal(popup.url, popup.image_url, popup.content, popup
                            .button_text);
                    });
                })
                .catch(error => {
                    console.error('Error fetching popups:', error.message);
                });
        }

        @if (!session('first_visit_popup') || !request()->cookie('daily_popup_showed'))
            fetchPopups();
        @endif
    });
</script>
