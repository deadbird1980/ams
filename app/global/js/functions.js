$(document).ready(function() {

    if ($('a.submit').length > 0) {

        $('a.submit').bind('click', function(e) {
            var $form = $('form').first().data('Zebra_Form');
            if ($form.validate()) {

                $form.submit();
            }
        });

    }
});
