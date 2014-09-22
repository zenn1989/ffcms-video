<h1>{{ extension.title }}<small>{{ language.admin_components_video_delete_item_title }}</small></h1>
<hr />

<p>{{ language.admin_components_video_delete_notify_warning }}</p>
<table class="table table-bordered table-responsive">
    <thead>
    <tr>
        <th>ID</th>
        <th>{{ language.admin_components_video_delete_th_name }}</th>
        <th>{{ language.admin_components_video_delete_th_path }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ video.id }}</td>
        <td>{{ video.title }}</td>
        <td>{{ video.pathway }}</td>
    </tr>
    </tbody>
</table>
<form method="post" action="">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="submit" value="{{ language.admin_components_video_delete_btn_delete }}" class="btn btn-danger"/>
    <a href="?object=components&action=video" class="btn btn-success">{{ language.admin_components_video_delete_btn_cancel }}</a>
</form>