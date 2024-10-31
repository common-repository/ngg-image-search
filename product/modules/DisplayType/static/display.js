document.addEventListener('DOMContentLoaded', function() {
    var ngg_fis_set_span_class = function(image, container) {
        var h = image.naturalHeight;
        var w = image.naturalWidth;
        if ((w > h) && (w >= (h * 1.50))) {
            container.classList.add('ngg-fis-wide');
        }
    };

    var ngg_fis_set_span_class_event = function(event) {
        var image = event.target;
        var container = image.closest('a');
        ngg_fis_set_span_class(image, container);

    };

    var gallery_containers = document.querySelectorAll('.imagely-ngg-image-search');
    for (var i = 0; i < gallery_containers.length; i++) {
        var image_containers = gallery_containers[i].getElementsByTagName('a');
        for (var n = 0; n < image_containers.length; n++) {
            var image = image_containers[n].getElementsByTagName('img')[0];
            if (image.complete) {
                ngg_fis_set_span_class(image, image_containers[n]);
            } else {
                image.addEventListener('load', ngg_fis_set_span_class_event);
            }
        }
    }
});