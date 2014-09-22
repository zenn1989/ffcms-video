{% import 'macro/scriptdata.tpl' as scriptdata %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_video_manage_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-12">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-default">{{ language.admin_components_video_list_filter_title }}</button>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="?object=components&action=video&make=list&filter=0">{{ language.admin_components_video_list_filter_all }}</a></li>
                    <li><a href="?object=components&action=video&make=list&filter=1">{{ language.admin_components_video_list_filter_moderate }}</a></li>
                    <li><a href="?object=components&action=video&make=list&filter=2">{{ language.admin_components_video_list_filter_impotant }}</a></li>
                </ul>
            </div>
        </div>
        <div class="pull-right">
            <form action="" method="get" class="form-inline">
                <input type="hidden" name="object" value="components" />
                <input type="hidden" name="action" value="video" />
                <input type="hidden" name="filter" value="3" />
                <div class="form-group">
                    <input type="text" name="search" placeHolder="Data..." value="{{ search.value|escape|striptags }}" class="form-control"/>
                </div>
                <div class="form-group">
                    <input value="Поиск" type="submit" name="dosearch" class="btn btn-primary"/>
                </div>
            </form>
        </div>
    </div>
</div>
{% if video %}
<form action="" method="post" onsubmit="return confirm('{{ language.admin_onsubmit_warning }}');">
    <table class="table table-bordered table-responsive">
        <thead>
        <tr>
            <th>ID</th>
            <th>{{ language.admin_components_video_list_table_title }}</th>
            <th>{{ language.admin_components_video_list_table_link }}</th>
            <th>{{ language.admin_components_video_list_table_date }}</th>
            <th>{{ language.admin_components_video_list_table_manage }}</th>
        </tr>
        </thead>
        <tbody>
        {% for row in video %}
            <tr>
                <td><input type="checkbox" name="check_array[]" class="check_array" value="{{ row.id }}"/> {{ row.id }}</td>
                <td>{% if row.important %}<i class="fa fa-paperclip" style="color:#ff0000;"></i> {% endif %}{% if row.moderate %}<i class="fa fa-eye-slash"></i> {% endif %}<a href="?object=components&action=video&make=edit&id={{ row.id }}">{{ row.title }}</a></td>
                <td><a href="{{ system.url }}/video/{{ row.link }}" target="_blank">/video/{{ row.link }}</a></td>
                <td>{{ row.date }}</td>
                <td class="text-center">
                    <a href="?object=components&action=video&make=edit&id={{ row.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
                    <a href="?object=components&action=video&make=delete&id={{ row.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    <a id="checkAll" class="btn btn-default">{{ language.admin_components_video_list_del_selectall }}</a>
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="deleteSelected" value="{{ language.admin_components_video_list_del_selected }}" class="btn btn-danger" />
    {{ scriptdata.checkjs('#checkAll', '.check_array') }}
</form>
{% if search.value|length < 1 %}
{{ pagination }}
{% endif %}
{% else %}
    {{ notifytpl.warning(language.admin_components_video_list_empty) }}
{% endif %}