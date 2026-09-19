/*
 * Lesson Studio (lessons/create).
 * Server data arrives via window.lessonStudio (see content_lessons/create.blade.php).
 * The server-side FormRequest stays authoritative; this file only improves the experience.
 */
(function ($) {
    'use strict';

    const cfg = window.lessonStudio || {};
    const $form = $('#lessonForm');
    if (!$form.length) return;

    let dirty = false;
    let submitting = false;
    let lastSubmitter = null;

    /* ------------------------------------------------------------------ helpers */
    const icon = (name, cls) =>
        '<svg class="lu ' + (cls || '') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
        'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#' + name + '"></use></svg>';
    const esc = (s) => $('<div>').text(s == null ? '' : String(s)).html();
    const currentType = () => $('input[name="content_type"]:checked').val() || 'video';
    /* Toggle only real form controls (not Summernote's generated toolbar buttons). */
    const setEnabled = ($scope, on) => $scope.find('input[name], select[name], textarea[name]').prop('disabled', !on);

    function toast(kind, message, ms) {
        const $t = $(
            '<div class="ls-toast ' + kind + '" role="status">' +
            icon(kind === 'success' ? 'circle-check' : 'circle-alert') +
            '<div class="ls-toast-text"></div>' +
            '<button type="button" class="ls-toast-close" aria-label="Dismiss">' + icon('x') + '</button></div>'
        );
        $t.find('.ls-toast-text').text(message);
        $t.find('.ls-toast-close').on('click', () => $t.remove());
        $('#toasts').append($t);
        setTimeout(() => $t.fadeOut(200, () => $t.remove()), ms || 6000);
    }

    function setDirty(value) {
        dirty = value;
        $('#dirtyDot').toggleClass('is-dirty', value);
        $('#dirtyText').text(value ? 'Unsaved changes' : 'No changes yet');
    }

    /* Inline validation messages live inside the .ls-field wrapper of each control. */
    function fieldFor(key) { return $('[data-field="' + key + '"]').first(); }
    function clearError($field) {
        $field.removeClass('has-error')
            .find('.is-invalid').removeClass('is-invalid').end()
            .children('.ls-error').remove();
    }
    function setError($field, message) {
        clearError($field);
        $field.addClass('has-error').find('.ls-control, .ls-dropzone, .ls-editor').addClass('is-invalid');
        const $err = $('<p class="ls-error" role="alert">' + icon('circle-alert') + ' <span></span></p>');
        $err.find('span').text(message);
        const $help = $field.children('.ls-help');
        $help.length ? $err.insertBefore($help) : $field.append($err);
    }

    /* ------------------------------------------------------------------ toolbar editor (Summernote) */
    const $body = $('#bodyEditor');
    function initEditor() {
        if (!$body.length || !$.fn.summernote) {
            $('#editorShell').removeClass('is-loading');
            return;
        }
        // $.summernote.ui only exists once the editor initialises, so buttons resolve it lazily.
        $body.summernote({
            height: 340,
            placeholder: 'Write the lesson content...',
            toolbar: [
                ['history', ['undo', 'redo']],
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol']],
                ['insert', ['link', 'picture', 'table']],
                ['blocks', ['quote', 'inlineCode']],
                ['view', ['codeview', 'fullscreen']],
            ],
            styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
            buttons: {
                quote: (context) => $.summernote.ui.button({
                    contents: icon('quote'),
                    tooltip: 'Quote',
                    click: () => context.invoke('editor.formatBlock', 'blockquote'),
                }).render(),
                inlineCode: (context) => $.summernote.ui.button({
                    contents: icon('code-xml'),
                    tooltip: 'Inline code',
                    click: () => {
                        const text = context.invoke('editor.createRange').toString() || 'code';
                        context.invoke('editor.pasteHTML', '<code>' + esc(text) + '</code>&nbsp;');
                    },
                }).render(),
            },
            callbacks: {
                onChange: function () {
                    if (!submitting) setDirty(true);
                    clearError(fieldFor('body'));
                },
            },
        });
        $('#editorShell').removeClass('is-loading');
    }

    /* ------------------------------------------------------------------ course / module */
    let positionEdited = false;

    function fillModules(courseId, keepId) {
        const $module = $('#moduleSelect');
        $module.empty().append(new Option(courseId ? 'Select a module' : 'Select a course first', ''));
        (cfg.modules || [])
            .filter((m) => courseId && String(m.course_id) === String(courseId))
            .forEach((m) => $module.append(new Option(m.label, m.id)));
        if (keepId && $module.find('option[value="' + keepId + '"]').length) $module.val(String(keepId));
        updateCrumbs();
    }

    function updateCrumbs() {
        const courseId = $('#courseSelect').val();
        const moduleLabel = $('#moduleSelect option:selected').val() ? $('#moduleSelect option:selected').text() : '';
        $('#crumbCourse').text((cfg.courses || {})[courseId] || 'Select a course');
        $('#crumbModule').text(moduleLabel || 'Select a module');
    }

    /* ------------------------------------------------------------------ slug */
    const existingSlugs = new Set(cfg.existingSlugs || []);
    let slugEdited = Boolean($('#slugInput').val());

    function slugify(value) {
        return String(value).toLowerCase().trim()
            .replace(/[^a-z0-9ក-៿\s-]/g, '')
            .replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
    function slugIsTaken() {
        const slug = $('#slugInput').val().trim();
        return slug !== '' && existingSlugs.has(slug);
    }
    function updateSlugStatus() {
        const slug = $('#slugInput').val().trim();
        const $badge = $('#slugStatus').removeClass('ls-badge-success ls-badge-danger ls-badge-muted');
        if (!slug) $badge.addClass('ls-badge-muted').text('Auto');
        else if (slugIsTaken()) $badge.addClass('ls-badge-danger').text('Taken');
        else $badge.addClass('ls-badge-success').text('Unique');
    }

    /* ------------------------------------------------------------------ duration (unit picker -> minutes) */
    function syncDuration() {
        const value = parseFloat($('#durationValue').val());
        if (isNaN(value) || value < 0) {
            $('#durationMinutes').val('');
            return;
        }
        $('#durationMinutes').val(Math.round($('#durationUnit').val() === 'hours' ? value * 60 : value));
    }

    /* ------------------------------------------------------------------ content type */
    function syncSegmented() {
        $('.ls-seg').each(function () {
            $(this).toggleClass('active', $(this).find('input').is(':checked'));
        });
    }

    function buildCompletion(type, preferred) {
        const options = (cfg.completion || {})[type] || {};
        const $select = $('#completionType');
        const wanted = preferred || $select.val();
        $select.empty();
        Object.keys(options).forEach((value) => $select.append(new Option(options[value], value)));
        $select.val(options[wanted] ? wanted : Object.keys(options)[0]);
        toggleMinimum();
    }

    function toggleMinimum() {
        const on = currentType() === 'video' && $('#completionType').val() === 'video_watched';
        setEnabled(fieldFor('minimum_watch_percentage').prop('hidden', !on), on);
    }

    /* Panes that aren't shown are disabled too, so their values are never submitted. */
    function applyType() {
        const type = currentType();
        syncSegmented();
        $('.ls-pane').each(function () {
            const on = $(this).data('pane') === type;
            setEnabled($(this).prop('hidden', !on), on);
        });
        applySource();
        buildCompletion(type);
    }

    /* ------------------------------------------------------------------ video source + preview */
    const PLACEHOLDERS = {
        youtube: 'https://youtube.com/watch?v=...',
        vimeo: 'https://vimeo.com/123456789',
        external: 'https://example.com/lesson.mp4',
    };

    function applySource() {
        if (currentType() !== 'video') return;
        const source = $('#videoSource').val();
        const upload = source === 'upload';
        setEnabled($('[data-source="url"]').prop('hidden', upload), !upload);
        setEnabled($('[data-source="upload"]').prop('hidden', !upload), upload);
        if (!upload) $('#videoUrl').attr('placeholder', PLACEHOLDERS[source] || PLACEHOLDERS.external);
        renderPreview();
    }

    function youtubeId(url) {
        const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/))([\w-]{11})/i);
        return m ? m[1] : null;
    }
    function vimeoId(url) {
        const m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
        return m ? m[1] : null;
    }

    let previewObjectUrl = null;
    let thumbObjectUrl = null;
    function revoke(url) { if (url) URL.revokeObjectURL(url); }

    function renderPreview() {
        const $preview = $('#videoPreview');
        if (!$preview.length) return;
        const source = $('#videoSource').val();
        const emptyMessage = (text) =>
            '<div class="ls-video-empty">' + icon('circle-play') + '<span>' + esc(text) + '</span></div>';
        let html = '';
        let message = source === 'upload' ? 'Choose a video file to preview it here' : 'Paste a video URL to preview it here';

        revoke(previewObjectUrl);
        previewObjectUrl = null;

        if (source === 'upload') {
            const file = $('input[name="video_upload"]')[0].files[0];
            if (file) {
                previewObjectUrl = URL.createObjectURL(file);
                html = '<video controls preload="metadata" src="' + previewObjectUrl + '"></video>';
            }
        } else {
            const url = ($('#videoUrl').val() || '').trim();
            if (url) {
                if (source === 'youtube') {
                    const id = youtubeId(url);
                    html = id
                        ? '<iframe src="https://www.youtube-nocookie.com/embed/' + id + '" title="Video preview" loading="lazy" allowfullscreen></iframe>'
                        : '';
                    if (!id) message = "This doesn't look like a valid YouTube link";
                } else if (source === 'vimeo') {
                    const id = vimeoId(url);
                    html = id
                        ? '<iframe src="https://player.vimeo.com/video/' + id + '" title="Video preview" loading="lazy" allowfullscreen></iframe>'
                        : '';
                    if (!id) message = "This doesn't look like a valid Vimeo link";
                } else if (/^https?:\/\//i.test(url)) {
                    html = '<video controls preload="metadata" src="' + esc(url) + '"></video>';
                } else {
                    message = 'Enter a full URL starting with http:// or https://';
                }
            }
        }

        $preview.html(html || emptyMessage(message));
        const poster = $preview.data('poster');
        $preview.toggleClass('has-poster', !html && Boolean(poster));
        $preview.css('background-image', !html && poster ? 'url("' + poster + '")' : '');

        // Uploaded file: suggest a duration if the teacher hasn't typed one.
        $preview.find('video').one('loadedmetadata', function () {
            if (source === 'upload' && !$('#durationValue').val() && isFinite(this.duration)) {
                $('#durationUnit').val('minutes');
                $('#durationValue').val(Math.max(1, Math.round(this.duration / 60)));
                syncDuration();
            }
        });
    }

    /* ------------------------------------------------------------------ dropzones & file lists */
    const attachments = typeof DataTransfer !== 'undefined' ? new DataTransfer() : null;

    function formatSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }
    function fileIcon(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext)) return 'file-image';
        if (['zip', 'rar', '7z', 'gz'].includes(ext)) return 'file-archive';
        if (['xls', 'xlsx', 'csv'].includes(ext)) return 'file-spreadsheet';
        if (['ppt', 'pptx'].includes(ext)) return 'presentation';
        if (['mp4', 'mov', 'avi', 'webm'].includes(ext)) return 'video';
        return 'file-text';
    }
    function acceptsFile(file, accept) {
        if (!accept) return true;
        return accept.split(',').map((t) => t.trim().toLowerCase()).filter(Boolean).some((token) => {
            if (token.startsWith('.')) return file.name.toLowerCase().endsWith(token);
            if (token.endsWith('/*')) return file.type.toLowerCase().startsWith(token.slice(0, -1));
            return file.type.toLowerCase() === token;
        });
    }
    function setInputFiles(input, files) {
        if (!attachments) return;
        const dt = new DataTransfer();
        files.forEach((f) => dt.items.add(f));
        input.files = dt.files;
    }

    function renderFiles($zone, files) {
        const kind = $zone.data('dropzone');
        const $list = $('[data-file-list="' + kind + '"]');
        $list.empty();
        files.forEach((file, index) => {
            const $row = $(
                '<li class="ls-file"><span class="ls-file-icon">' + icon(fileIcon(file.name)) + '</span>' +
                '<div class="ls-file-info"><span class="ls-file-name"></span><span class="ls-file-meta"></span></div>' +
                '<button type="button" class="ls-file-remove" aria-label="Remove file">' + icon('trash-2') + '</button></li>'
            );
            $row.find('.ls-file-name').text(file.name);
            $row.find('.ls-file-meta').text(formatSize(file.size) + ' · ' + ((file.name.split('.').pop() || 'file').toUpperCase()));
            $row.find('.ls-file-remove').on('click', () => removeFile($zone, index));
            $list.append($row);
        });
    }

    function removeFile($zone, index) {
        const input = $zone.find('input[type=file]')[0];
        const kind = $zone.data('dropzone');
        if (kind === 'attachments' && attachments) {
            const kept = Array.from(attachments.files).filter((_, i) => i !== index);
            while (attachments.items.length) attachments.items.remove(0);
            kept.forEach((f) => attachments.items.add(f));
            input.files = attachments.files;
            renderFiles($zone, Array.from(attachments.files));
        } else {
            input.value = '';
            renderFiles($zone, []);
            if (kind === 'video') renderPreview();
            if (kind === 'thumb') updatePoster();
        }
        setDirty(true);
    }

    function handleFiles($zone, incoming) {
        const input = $zone.find('input[type=file]')[0];
        const kind = $zone.data('dropzone');
        const maxMb = parseFloat($(input).data('max-mb')) || 0;
        const accept = $(input).attr('accept');
        const $field = $zone.closest('.ls-field');
        const rejected = [];
        const accepted = [];

        Array.from(incoming).forEach((file) => {
            if (maxMb && file.size > maxMb * 1048576) rejected.push(file.name + ' is larger than ' + maxMb + ' MB.');
            else if (!acceptsFile(file, accept)) rejected.push(file.name + ' is not an allowed file type.');
            else accepted.push(file);
        });

        clearError($field);
        if (rejected.length) setError($field, rejected[0] + (rejected.length > 1 ? ' (+' + (rejected.length - 1) + ' more)' : ''));

        if (kind === 'attachments') {
            if (!attachments) return;
            accepted.forEach((file) => {
                const duplicate = Array.from(attachments.files).some(
                    (f) => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
                );
                if (!duplicate) attachments.items.add(file);
            });
            input.files = attachments.files;
            renderFiles($zone, Array.from(attachments.files));
        } else {
            const file = accepted[0];
            if (file) {
                setInputFiles(input, [file]);
                renderFiles($zone, [file]);
            } else {
                input.value = '';
                renderFiles($zone, []);
            }
            if (kind === 'video') renderPreview();
            if (kind === 'thumb') updatePoster();
        }
        setDirty(true);
    }

    function updatePoster() {
        revoke(thumbObjectUrl);
        thumbObjectUrl = null;
        const file = $('input[name="video_thumbnail"]')[0].files[0];
        if (file) thumbObjectUrl = URL.createObjectURL(file);
        $('#videoPreview').data('poster', thumbObjectUrl || '');
        renderPreview();
    }

    function initDropzones() {
        $('[data-dropzone]').each(function () {
            const $zone = $(this);
            const input = $zone.find('input[type=file]')[0];

            $zone.on('dragenter dragover', (e) => { e.preventDefault(); $zone.addClass('dragover'); });
            $zone.on('dragleave dragend drop', () => $zone.removeClass('dragover'));
            $zone.on('drop', (e) => {
                e.preventDefault();
                const files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
                if (files && files.length) handleFiles($zone, files);
            });
            $(input).on('change', function () {
                // Browse dialog: input.files holds only the fresh pick; merge/validate it.
                if (this.files && this.files.length) handleFiles($zone, this.files);
                else if ($zone.data('dropzone') !== 'attachments') renderFiles($zone, []);
            });
        });
    }

    /* ------------------------------------------------------------------ validation (client-side mirror) */
    function validate() {
        const problems = [];
        const add = (key, message) => problems.push({ key, message });
        const type = currentType();
        const isUrl = (v) => { try { const u = new URL(v); return u.protocol === 'http:' || u.protocol === 'https:'; } catch (e) { return false; } };

        if (!$('#courseSelect').val()) add('course_id', 'Select a course.');
        if (!$('#moduleSelect').val()) add('course_module_id', 'Select a module.');
        if ($('#titleInput').val().trim().length < 3) add('title', 'Enter a lesson title (at least 3 characters).');
        if (slugIsTaken()) add('slug', 'This slug is already used by another lesson.');
        const position = parseInt($('#positionInput').val(), 10);
        if (isNaN(position) || position < 1) add('position', 'Order must be 1 or higher.');

        if (type === 'video') {
            if ($('#videoSource').val() === 'upload') {
                if (!$('input[name="video_upload"]')[0].files.length) add('video_upload', 'Choose a video file to upload.');
            } else {
                const url = $('#videoUrl').val().trim();
                if (!url) add('video_url', 'Enter the video URL.');
                else if (!isUrl(url)) add('video_url', 'Enter a valid URL starting with http:// or https://');
            }
        } else if (type === 'lesson') {
            const html = $body.length && $.fn.summernote ? $body.summernote('code') : $body.val();
            const text = $('<div>').html(html || '').text().trim();
            if (!text && !/<(img|table|iframe|video)/i.test(html || '')) add('body', 'Write the lesson content.');
        } else if (type === 'file') {
            if (!$('input[name="document[document_file]"]')[0].files.length) add('document.document_file', 'Choose a file to upload.');
        } else if (type === 'url') {
            const url = $('#externalUrl').val().trim();
            if (!url) add('external_url', 'Enter the link URL.');
            else if (!isUrl(url)) add('external_url', 'Enter a valid URL starting with http:// or https://');
        }
        return problems;
    }

    function focusFirstError() {
        const $first = $('.ls-field.has-error').first();
        if (!$first.length) return;
        $first[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        const $control = $first.find('.ls-control, input, select, textarea').filter(':visible').first();
        if ($control.length) $control[0].focus({ preventScroll: true });
        else if ($first.find('.ls-editor').length && $.fn.summernote) $body.summernote('focus');
    }

    /* ------------------------------------------------------------------ events */
    function bind() {
        // dirty tracking + clearing an error as soon as the user edits that field
        $form.on('input change', ':input', function () {
            if (!submitting) setDirty(true);
            const $field = $(this).closest('.ls-field');
            if ($field.hasClass('has-error') && !$(this).is('[type=file]')) clearError($field);
        });

        $('#courseSelect').on('change', function () {
            fillModules($(this).val(), null);
            $('#moduleSelect').trigger('change');
        });
        $('#moduleSelect').on('change', function () {
            updateCrumbs();
            const module = (cfg.modules || []).find((m) => String(m.id) === String($(this).val()));
            if (module && !positionEdited) $('#positionInput').val(module.next_position);
        });
        $('#positionInput').on('input', () => { positionEdited = true; });

        $('#titleInput').on('input', function () {
            if (!slugEdited) {
                $('#slugInput').val(slugify(this.value));
                updateSlugStatus();
            }
        });
        $('#slugInput').on('input', function () {
            slugEdited = $(this).val() !== '';
            updateSlugStatus();
        });
        $('#slugInput').on('blur', function () {
            $(this).val(slugify($(this).val()));
            updateSlugStatus();
        });

        $('input[name="content_type"]').on('change', applyType);
        $('#videoSource').on('change', applySource);
        let previewTimer;
        $('#videoUrl').on('input', () => { clearTimeout(previewTimer); previewTimer = setTimeout(renderPreview, 300); });

        $('#completionType').on('change', toggleMinimum);
        $('#durationValue, #durationUnit').on('input change', syncDuration);

        $('input[name="status"]').on('change', function () {
            syncSegmented();
            $('#statusHelp').text(this.value === 'published'
                ? 'Published lessons are visible to enrolled students.'
                : 'Draft lessons are hidden from students.');
        });

        $form.on('click', 'button[type="submit"]', function () {
            lastSubmitter = this;
            const forced = $(this).data('force-status');
            if (forced) $('input[name="status"][value="' + forced + '"]').prop('checked', true).trigger('change');
        });

        $form.on('submit', function (e) {
            if (submitting) { e.preventDefault(); return; }

            $('.ls-field.has-error').each(function () { clearError($(this)); });
            const problems = validate();
            if (problems.length) {
                e.preventDefault();
                problems.forEach((p) => setError(fieldFor(p.key), p.message));
                toast('error', problems.length === 1 ? 'Please fix the highlighted field.' : 'Please fix the ' + problems.length + ' highlighted fields.');
                focusFirstError();
                return;
            }

            if ($body.length && $.fn.summernote && $body.summernote('codeview.isActivated')) {
                $body.summernote('codeview.deactivate'); // flush code view back into the textarea
            }
            syncDuration();

            submitting = true;
            const submitter = (e.originalEvent && e.originalEvent.submitter) || lastSubmitter;
            $form.attr('aria-busy', 'true');
            $('.ls-bar-actions .ls-btn').addClass('is-busy').attr('aria-disabled', 'true');
            $('.ls-bar-actions button').prop('disabled', true);
            if (submitter) {
                $(submitter).find('span').text('Saving...');
            }
        });

        window.addEventListener('beforeunload', function (e) {
            if (dirty && !submitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        // Back/forward cache can restore the page with the buttons still locked.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) window.location.reload();
        });
    }

    /* ------------------------------------------------------------------ boot */
    initEditor();
    initDropzones();
    fillModules($('#courseSelect').val(), $('#moduleSelect').val());
    syncDuration();
    updateSlugStatus();
    syncSegmented();
    applyType();
    if (cfg.oldCompletion) buildCompletion(currentType(), cfg.oldCompletion);
    $('.ls-field.has-error .ls-editor').addClass('is-invalid');
    bind();

    if (cfg.flash && cfg.flash.success) toast('success', cfg.flash.success, 8000);
    const serverErrors = cfg.flash ? cfg.flash.errorCount : 0;
    if (serverErrors) {
        toast('error', 'The lesson was not saved. Please fix the highlighted ' + (serverErrors === 1 ? 'field.' : 'fields.'));
        focusFirstError();
        setDirty(true);
    } else {
        setDirty(false);
    }
})(jQuery);
