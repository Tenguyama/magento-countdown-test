define(['jquery', 'Magento_Catalog/js/jquery.countdown'], function($){
    $(document).ready(function(){
        console.log('jQuery is working!');
        var endDate = $('#special-price-timer').data('end-date');
        if(endDate){

            $('#special-price-timer').countdown(endDate, function(event) {
                var html = `
                    <div class="countdown-item">
                        <span class="countdown-item__value">
                            ${event.strftime('%D')}
                        </span>
                        <span class="countdown-item__title">
                            Days
                        </span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-item__value">
                            ${event.strftime('%H')}
                        </span>
                        <span class="countdown-item__title">
                            Hours
                        </span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-item__value">
                            ${event.strftime('%M')}
                        </span>
                        <span class="countdown-item__title">
                            Minutes
                        </span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-item__value">
                            ${event.strftime('%S')}
                        </span>
                        <span class="countdown-item__title">
                            Seconds
                        </span>
                    </div>
                `;
                $(this).html(html);
            });
        }
    });
});
