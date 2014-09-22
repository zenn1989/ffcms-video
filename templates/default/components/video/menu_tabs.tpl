<ul class="nav nav-tabs nav-justified" role="tablist">
    <li{% if video_sort_type == 'all' %} class="active"{% endif %}><a href="{{ system.url }}/video/"><i class="fa fa-video-camera"></i> {{ language.video_menutab_all }}</a></li>
    <li{% if video_sort_type == 'top' %} class="active"{% endif %}><a href="{{ system.url }}/video/top/"><i class="fa fa-thumbs-up"></i> {{ language.video_menutab_top }}</a></li>
    <li{% if video_sort_type == 'categorys' %} class="active"{% endif %}><a href="{{ system.url }}/video/categorys/"><i class="fa fa-folder-open"></i> {{ language.video_menutab_cats }}</a></li>
</ul>