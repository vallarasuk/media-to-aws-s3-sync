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

    // Bulk Sync Logic
    $('#m2s3-bulk-sync-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $('#m2s3-bulk-sync-spinner');
        var $progressContainer = $('#m2s3-bulk-sync-progress-container');
        var $progressFill = $('#m2s3-bulk-sync-progress-fill');
        var $statusText = $('#m2s3-bulk-sync-status-text');
        var $logBox = $('#m2s3-bulk-sync-log');

        var ajaxUrl = window.media_to_aws_s3_sync_vars && window.media_to_aws_s3_sync_vars.ajaxurl ? window.media_to_aws_s3_sync_vars.ajaxurl : (window.ajaxurl || '/wp-admin/admin-ajax.php');
        var nonce = window.media_to_aws_s3_sync_vars && window.media_to_aws_s3_sync_vars.nonce ? window.media_to_aws_s3_sync_vars.nonce : '';

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        $logBox.empty().append('<div class="log-info">Starting Bulk Sync process...</div>');
        $progressContainer.show();
        $progressFill.css('width', '0%');
        $statusText.text('Querying unsynced attachments...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'media_to_aws_s3_bulk_sync_get_unsynced',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var ids = response.data.ids;
                    var total = response.data.total;
                    
                    if (total === 0) {
                        $spinner.removeClass('is-active');
                        $btn.prop('disabled', false);
                        $statusText.text('All media is already synced!');
                        $logBox.append('<div class="log-success">No unsynced attachments found.</div>');
                        return;
                    }

                    $statusText.text('Processing: 0 / ' + total);
                    $logBox.append('<div class="log-info">Found ' + total + ' unsynced attachments. Beginning upload...</div>');
                    processBatch(ids, total, 0, ajaxUrl, nonce);
                } else {
                    $spinner.removeClass('is-active');
                    $btn.prop('disabled', false);
                    $logBox.append('<div class="log-error">Error: ' + (response.data.message || 'Failed to query attachments.') + '</div>');
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);
                $logBox.append('<div class="log-error">AJAX Error occurred while querying attachments.</div>');
            }
        });

        function processBatch(ids, total, processedCount, ajaxUrl, nonce) {
            if (ids.length === 0) {
                $spinner.removeClass('is-active');
                $btn.prop('disabled', false);
                $logBox.append('<div class="log-success">Bulk Sync Completed!</div>');
                return;
            }

            var batchSize = 5;
            var currentBatch = ids.splice(0, batchSize);

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'media_to_aws_s3_bulk_sync_process_batch',
                    attachment_ids: currentBatch,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.results) {
                        $.each(response.data.results, function(index, result) {
                            processedCount++;
                            var logClass = result.status === 'success' ? 'log-success' : 'log-error';
                            $logBox.append('<div class="' + logClass + '">ID ' + result.id + ': ' + result.message + '</div>');
                        });
                        
                        var percentage = Math.round((processedCount / total) * 100);
                        $progressFill.css('width', percentage + '%');
                        $statusText.text('Processing: ' + processedCount + ' / ' + total + ' (' + percentage + '%)');
                        $logBox.scrollTop($logBox[0].scrollHeight);

                        // Call the next batch
                        processBatch(ids, total, processedCount, ajaxUrl, nonce);
                    } else {
                        $logBox.append('<div class="log-error">Batch Error: ' + (response.data ? response.data.message : 'Unknown error') + '</div>');
                        $spinner.removeClass('is-active');
                        $btn.prop('disabled', false);
                        $logBox.append('<div class="log-error">Bulk Sync aborted due to error.</div>');
                    }
                },
                error: function() {
                    $logBox.append('<div class="log-error">AJAX Error occurred while processing batch.</div>');
                    $spinner.removeClass('is-active');
                    $btn.prop('disabled', false);
                }
            });
        }
    });
});
