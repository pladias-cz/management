import $ from 'jquery';
import 'jquery-ui/ui/widgets/autocomplete';

export default function autocomplete() {

    $(".autocomplete").each(function () {
        $(this).autocomplete({
            source: $(this).data('source'),
            minLength: 1,
            select: function (event, ui) {
                // console.log( ui.item);
                // console.log($(this).data('target')+ '/' + ui.item.value);
            }
        });

    });
}
