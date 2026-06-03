jQuery(document).ready(function($) {
    // Helper function to display toast notifications
    function showToast(message, type, linkUrl) {
        var $toastContainer = $('#media-to-aws-s3-sync-toast-container');
        if ($toastContainer.length === 0) {
            $toastContainer = $('<div id="media-to-aws-s3-sync-toast-container"></div>');
            $('body').append($toastContainer);
        }

        var icon = type === 'success' ? '✓' : '✗';
        var toastClass = type === 'success' ? 'success' : 'error';
        var $toast = $('<div class="media-to-aws-s3-sync-toast ' + toastClass + '"></div>');
        
        var $header = $('<div class="media-to-aws-s3-sync-toast-header">' + icon + ' <span style="margin-left: 8px;">' + message + '</span></div>');
        $toast.append($header);

        if (linkUrl) {
            var $link = $('<a href="' + linkUrl + '" target="_blank" class="media-to-aws-s3-sync-toast-link">View S3 File</a>');
            $toast.append($link);
        }

        $toastContainer.append($toast);

        setTimeout(function() {
            $toast.css({
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, 50);

        setTimeout(function() {
            $toast.css({
                'opacity': '0',
                'transform': 'translateY(-20px)'
            });
            setTimeout(function() {
                $toast.remove();
            }, 300);
        }, 5000);
    }

    // Sync to S3 / Re-sync to S3 button click handler via event delegation
    $(document).on('click', '.media-to-aws-s3-sync-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var $container = $btn.closest('.media-to-aws-s3-sync-container');
        var attachmentId = $container.data('attachment-id');
        var $spinner = $container.find('.spinner');

        // Check if configuration is missing
        if ($container.data('configured') === 0 || $container.data('configured') === '0') {
            showToast('AWS S3 is not configured. Please enter your credentials in Settings > Media to S3 Sync.', 'error');
            return;
        }

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        var ajaxUrl = window.media_to_aws_s3_sync_vars && window.media_to_aws_s3_sync_vars.ajaxurl ? window.media_to_aws_s3_sync_vars.ajaxurl : (window.ajaxurl || '/wp-admin/admin-ajax.php');
        var nonce = window.media_to_aws_s3_sync_vars && window.media_to_aws_s3_sync_vars.nonce ? window.media_to_aws_s3_sync_vars.nonce : '';

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'media_to_aws_s3_sync_action',
                attachment_id: attachmentId,
                nonce: nonce
            },
            success: function(response) {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);
                if (response.success) {
                    $container.replaceWith(response.data.html);
                    showToast('Synced successfully!', 'success', response.data.s3_url);
                } else {
                    showToast(response.data.message || 'Sync failed.', 'error');
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);
                showToast('An error occurred during sync.', 'error');
            }
        });
    });

    // Copy to clipboard handler
    $(document).on('click', '.media-to-aws-s3-sync-copy-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('s3-url');

        if (!url) {
            // Try to find it in the input field as a fallback
            url = $btn.closest('.media-to-aws-s3-sync-container').find('.media-to-aws-s3-sync-url-input').val();
        }

        if (!url) {
            showToast('No S3 URL to copy.', 'error');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                showSuccess($btn);
            }, function() {
                fallbackCopy(url, $btn);
            });
        } else {
            fallbackCopy(url, $btn);
        }
    });

    function fallbackCopy(text, $btn) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.width = "2em";
        textArea.style.height = "2em";
        textArea.style.padding = "0";
        textArea.style.border = "none";
        textArea.style.outline = "none";
        textArea.style.boxShadow = "none";
        textArea.style.background = "transparent";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showSuccess($btn);
            } else {
                console.error('Fallback copy failed');
                showToast('Could not copy S3 URL to clipboard.', 'error');
            }
        } catch (err) {
            console.error('Fallback copy error', err);
            showToast('Could not copy S3 URL to clipboard.', 'error');
        }
        document.body.removeChild(textArea);
    }

    function showSuccess($btn) {
        var originalBg = $btn.css('background-color');
        var originalColor = $btn.css('color');
        var originalBorder = $btn.css('border-color');

        $btn.text('Copied!');
        $btn.css({
            'background-color': '#46b450',
            'color': '#ffffff',
            'border-color': '#46b450'
        });

        setTimeout(function() {
            $btn.text('Copy URL');
            $btn.css({
                'background-color': originalBg,
                'color': originalColor,
                'border-color': originalBorder
            });
        }, 2000);
    }
});
