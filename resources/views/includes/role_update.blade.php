<script language="JavaScript">
  function toggle(source) {
    checkboxes = document.getElementsByName('permission[]');
    for(var i=0, n=checkboxes.length;i<n;i++) {
      checkboxes[i].checked = source.checked;
    }
  }
</script>

<div class="col-md-12">
  <b><input type="checkbox" id="selectall" onClick="toggle(this)" /> TOGGLE ALL<br></b><br>
</div>

<div class="col-md-12 col-sm-12">
  @foreach($role_groups as $key => $value)
    @if($key < 4)
    <div class="col-md-3 col-sm-12">
      <label>{{$value->group}}:</label><br>
      @foreach($permissions->where('group', $value->group) as $k => $v)

        @php
          $checked = in_array($v->id, $rolePermissions) ? 'checked' : null;
        @endphp

        <input type="checkbox" class="foo" name="permission[]" value="{{$v->id}}" {{$checked}}> {{$v->display_name}}</br>
      @endforeach
    </div>
    @else
    @endif
  @endforeach
</div>

<div class="col-md-12 col-sm-12">
  @foreach($role_groups as $key => $value)
    @if($key > 3 && $key < 8)
    <div class="col-md-3 col-sm-12">
      <label>{{$value->group}}:</label><br>
      @foreach($permissions->where('group', $value->group) as $k => $v)

        @php
          $checked = in_array($v->id, $rolePermissions) ? 'checked' : null;
        @endphp

        <input type="checkbox" class="foo" name="permission[]" value="{{$v->id}}" {{$checked}}> {{$v->display_name}}</br>
      @endforeach
    </div>
    @else
    @endif
  @endforeach
</div>

<div class="col-md-12 col-sm-12">
  @foreach($role_groups as $key => $value)
    @if($key > 7 && $key < 12)
    <div class="col-md-3 col-sm-12">
      <label>{{$value->group}}:</label><br>
      @foreach($permissions->where('group', $value->group) as $k => $v)

        @php
          $checked = in_array($v->id, $rolePermissions) ? 'checked' : null;
        @endphp

        <input type="checkbox" class="foo" name="permission[]" value="{{$v->id}}" {{$checked}}> {{$v->display_name}}</br>
      @endforeach
    </div>
    @else
    @endif
  @endforeach
</div>

<div class="col-md-12 col-sm-12">
  @foreach($role_groups as $key => $value)
    @if($key > 11 && $key < 16)
    <div class="col-md-3 col-sm-12">
      <label>{{$value->group}}:</label><br>
      @foreach($permissions->where('group', $value->group) as $k => $v)

        @php
          $checked = in_array($v->id, $rolePermissions) ? 'checked' : null;
        @endphp

        <input type="checkbox" class="foo" name="permission[]" value="{{$v->id}}" {{$checked}}> {{$v->display_name}}</br>
      @endforeach
    </div>
    @else
    @endif
  @endforeach
</div>

<div class="col-md-12 col-sm-12">
  @foreach($role_groups as $key => $value)
    @if($key > 15 && $key < 20)
    <div class="col-md-3 col-sm-12">
      <label>{{$value->group}}:</label><br>
      @foreach($permissions->where('group', $value->group) as $k => $v)

        @php
          $checked = in_array($v->id, $rolePermissions) ? 'checked' : null;
        @endphp

        <input type="checkbox" class="foo" name="permission[]" value="{{$v->id}}" {{$checked}}> {{$v->display_name}}</br>
      @endforeach
    </div>
    @else
    @endif
  @endforeach
</div>