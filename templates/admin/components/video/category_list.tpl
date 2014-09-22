<h1>{{ extension.title }}<small>{{ language.admin_components_video_categorymanage_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-9">
        <div class="row">
            <div class="col-lg-9">
                <div class="alert alert-success">{{ language.admin_components_video_category_list_tab_name }}</div>
            </div>
            <div class="col-lg-3">
                <div class="alert alert-danger">{{ language.admin_components_video_category_list_tab_manage }}</div>
            </div>
        </div>
        {% for cat_data in video.categorys %}
            <div class="row">
                <div class="col-lg-9">
                    <div class="alert alert-info">
                        <div class="label label-danger">{{ cat_data.name }}</div> <div class="label label-success">[/{{ cat_data.path }}]</div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="text-center">
                        <a href="?object=components&action=video&make=addcategory&id={{ cat_data.id }}" class="btn btn-success"><i class="fa fa-plus"></i></a>
                        <a href="?object=components&action=video&make=editcategory&id={{ cat_data.id }}" class="btn btn-warning"><i class="fa fa-pencil"></i></a>
                        <a href="?object=components&action=video&make=delcategory&id={{ cat_data.id }}" class="btn btn-danger"><i class="fa fa-minus"></i></a>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
    <div class="col-lg-3">
        <h2>{{ language.admin_components_video_category_list_alt_title }}</h2>
        <p>{{ language.admin_components_video_category_list_alt_desc }}</p>
        <a href="?object=components&action=video&make=addcategory" class="btn btn-primary btn-block">{{ language.admin_components_video_category_list_alt_button }}</a>
    </div>
</div>