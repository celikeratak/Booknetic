(function ($)
{
	"use strict";

	$(document).ready(function() {
        $('#summernote').summernote({
            height: 300,
            prettifyHtml: false,
            codeviewFilter: false,
            codeviewIframeFilter: false
        });
    
        $('#saveButton').click(function() {
            var content = $('#summernote').summernote('code');
    
            // Extract <style> content
            var styleContent = '';
            var styleMatches = content.match(/<style[^>]*>([\s\S]*?)<\/style>/gi);
            if (styleMatches) {
                styleContent = styleMatches.join(''); // Combine all <style> tags
            }
    
            // Store content and style separately
            var cleanContent = content.replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '');
            var finalContent = styleContent + cleanContent;
    
            $('#savedContent').val(finalContent);
        });
    });

    var cleanHTML = DOMPurify.sanitize($('#summernote').summernote('code'), {
        ALLOWED_TAGS: ['style', 'div', 'span', 'p', 'b', 'i', 'u', 'h1', 'h2', 'h3'],
        ALLOWED_ATTR: ['style', 'class', 'id']
    });

    $.extend($.summernote.options, {
        codeviewFilter: false,
        codeviewIframeFilter: false
    });

    

})(jQuery);
