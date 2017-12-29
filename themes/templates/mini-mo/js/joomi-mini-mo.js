/* countdown product-list */
$(document).ready(function(){
    $('[data-countdown]')
    .each(function(){var $this=$(this),
        finalDate=$(this).data('countdown');
        $this.countdown(finalDate,function(event){
            var fomat='<span>%H</span><b></b><span>%M</span><b></b><span>%S</span>';
            $this.html(event.strftime(fomat));
        });
    });
    if($('.countdown-lastest').length>0){
        var labels=['Years','Months','Weeks','Days','Hrs','Mins','Secs'];
        var layout='<span class="count"><span class="num">{dnn}</span><span class="text">Дней</span></span><span class="dot">:</span><span class="count"><span class="num">{hnn}</span><span class="text">Часов</span></span><span class="dot">:</span><span class="count"><span class="num">{mnn}</span><span class="text">Минут</span></span><span class="dot">:</span><span class="count"><span class="num">{snn}</span><span class="text">Сек.</span></span>';
        $('.countdown-lastest').each(function(){
            var austDay=new
            Date(
            $(this).data('y'),
            $(this).data('m')-1,
            $(this).data('d'),
            $(this).data('h'),
            $(this).data('i'),
            $(this).data('s')
            );
            $(this).countdown({until:austDay,labels:labels,layout:layout});
        });
    }
});