<h1>{{ extension.title }}<small>{{ language.admin_components_video_categorymanage_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
<div class="table-responsive">
    <table class="table table-line table-striped table-hover">
        <thead>
        <tr>
            <th class="col-lg-10">{{ language.admin_components_video_category_list_tab_name }}</th>
            <th class="col-lg-2 text-center">{{ language.admin_components_video_category_list_tab_manage }}</th>
        </tr>
        </thead>
        <tbody>
        {% for cat_data in video.categorys %}
        <tr>
            <td>
                <div class="row">
                    {% if cat_data.level == 0 %}
                    <div class="col-lg-12">
                        <strong>{{ cat_data.name }}</strong> {# {{ cat_data.level }} #}
                    </div>
                    {% else %}
                        {% set html_level = cat_data.level + 2 %}
                        {% if html_level > 9 %}
                            {% set html_level = 9 %}
                        {% endif %}
                        <div class="col-lg-{{ html_level }}">
                            <div class="bg-info">
                                <div class="text-center">
                                    <strong>{{ cat_data.path }}<abbr title="level">[{{ cat_data.level }}]</abbr></strong>
                                    <span class="pull-right">&rarr;</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-{{ 12 - html_level }}">
                            {{ cat_data.name }}
                        </div>
                    {% endif %}
                </div>
            </td>
            <td class="text-center">
                <a href="?object=components&action=video&make=addcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-plus"></i></a>
                <a href="?object=components&action=video&make=editcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                {% if cat_data.level != 0 %}<a href="?object=components&action=video&make=delcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-minus"></i></a>{% endif %}
            </td>
        </tr>
        {% endfor %}
        </tbody>
    </table>
</div>

<h2>{{ language.admin_components_video_category_list_alt_title }}</h2>
<p>{{ language.admin_components_video_category_list_alt_desc }}</p>
<a href="?object=components&action=video&make=addcategory" class="btn btn-primary">{{ language.admin_components_video_category_list_alt_button }}</a>





<!--
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
</div> -->