<!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel" style="padding-bottom: 20px">
        <div class="pull-left image">
          <img src="{{Auth::user()->img_url}}" class="img-circle" alt="User Image" id="left-dp">
        </div>
        <div class="pull-left info">
          <p>{{Auth::user()->name}}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->

      @php
        $route = Route::currentRouteName();
      @endphp

      <ul class="sidebar-menu" data-widget="tree">
        <li @if($route == 'home') class="active" @else @endif>
          <a href="{{route('home')}}">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        @ability('Admin','View-Vendors')
        <li @if($route == 'vendors.index') class="active" @else @endif>
          <a href="{{route('vendors.index')}}">
            <i class="fa fa-address-book"></i> <span>Vendors</span>
          </a>
        </li>
        @endability

        @ability('Admin','View-Facilities,View-Floors,View-Apartments')
        <li @if($route == 'facilities.index' || $route == 'floors.index' || $route == 'apartments.index') class="active treeview" @else class="treeview" @endif>
          <a href="#">
            <i class="fa fa-th"></i> <span>Location Management</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @ability('Admin','View-Facilities')
            <li @if($route == 'facilities.index') class="active" @else @endif><a href="{{route('facilities.index')}}"><i class="fa fa-circle-o"></i> Facilities</a></li>
            @endability

            @ability('Admin','View-Floors')
            <li @if($route == 'floors.index') class="active" @else @endif><a href="{{route('floors.index')}}"><i class="fa fa-circle-o"></i> Floors</a></li>
            @endability

            @ability('Admin','View-Apartments')
            <li @if($route == 'apartments.index') class="active" @else @endif><a href="{{route('apartments.index')}}"><i class="fa fa-circle-o"></i> Units</a></li>
            @endability

          </ul>
        </li>
        @endability

        @ability('Admin','View-Clients,View-Preconditions,View-Admissions')
        <li @if($route == 'preconditions.index' || $route == 'clients.index' || $route == 'admissions.index') class="active treeview" @else class="treeview" @endif>
          <a href="#">
            <i class="fa fa-group"></i> <span>Client Management</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @ability('Admin','View-Preconditions')
            <li @if($route == 'preconditions.index') class="active" @else @endif><a href="{{route('preconditions.index')}}"><i class="fa fa-circle-o"></i> Client's Preconditions</a></li>
            @endability

            @ability('Admin','View-Clients')
            <li @if($route == 'clients.index') class="active" @else @endif><a href="{{route('clients.index')}}"><i class="fa fa-circle-o"></i> Clients</a></li>
            @endability

            @ability('Admin','View-Admissions')
            <li @if($route == 'admissions.index') class="active" @else @endif><a href="{{route('admissions.index')}}"><i class="fa fa-circle-o"></i> Admission</a></li>
            @endability

          </ul>
        </li>
        @endability


        @ability('Admin','View-Inspections,View-Deficiency-Categories,View-Deficiency-Concerns,View-Deficiency-Details-List')
        <li @if($route == 'deficiencydetails.index' || $route == 'deficiencycategories.index' || $route == 'deficiencyconcerns.index' || $route == 'inspections.index') class="active treeview" @else class="treeview" @endif>
          <a href="#">
            <i class="fa fa-files-o"></i> <span>Inspection Details</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @ability('Admin','View-Inspections')
            <li @if($route == 'inspections.index') class="active" @else @endif><a href="{{route('inspections.index')}}"><i class="fa fa-circle-o"></i> Inspections</a></li>
            @endability

            @ability('Admin','View-Deficiency-Concerns')
            <li @if($route == 'deficiencyconcerns.index') class="active" @else @endif><a href="{{route('deficiencyconcerns.index')}}"><i class="fa fa-circle-o"></i> Deficiency Concerns</a></li>
            @endability

            @ability('Admin','View-Deficiency-Categories')
            <li @if($route == 'deficiencycategories.index') class="active" @else @endif><a href="{{route('deficiencycategories.index')}}"><i class="fa fa-circle-o"></i> Deficiency Categories</a></li>
            @endability

            @ability('Admin','View-Deficiency-Details-List')
            <li @if($route == 'deficiencydetails.index') class="active" @else @endif><a href="{{route('deficiencydetails.index')}}"><i class="fa fa-circle-o"></i> Deficiency Details</a></li>
            @endability
          </ul>
        </li>
        @endability

        @ability('Admin','Show-Attendance,Create-Attendance')
        <li @if($route == 'attendances.index' || $route == 'attendances.show' || $route == 'attendances.showForm' || $route == 'attendances.showByDate' ) class="active treeview" @else class="treeview" @endif>

          <a href="#">
            <i class="fa fa-building"></i> <span>Sign In Management</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>

          <ul class="treeview-menu">
            @ability('Admin','Show-Attendance')
            <li @if($route == 'attendances.showByDate' ) class="active" @else @endif>
              <a href="{{route('attendances.showByDate',date('m-d-Y'))}}">
                <i class="fa fa-circle-o"></i> <span>Show</span>
              </a>
            </li>
            @endability
            @ability('Admin','Create-Attendance')
            <li @if($route == 'attendances.index' || $route == 'attendances.showForm') class="active" @else @endif>
              <a href="{{route('attendances.index')}}">
                <i class="fa fa-circle-o"></i> <span>Sign In Sheet</span>
              </a>
            </li>
            @endability
          </ul>
        </li>
        @endability
        
        @ability('Admin','View-Bill')
        <li @if($route == 'billings.index') class="active" @else @endif>
          <a href="{{route('billings.index')}}">
            <i class="fa fa-money"></i> <span>Billing</span>
          </a>
        </li>
        @endability

        @ability('Admin','View-Reports')
        <li @if($route == 'index.index') class="active" @else @endif>
          <a href="{{route('reports.index')}}">
            <i class="fa fa-file"></i> <span>Reports</span>
          </a>
        </li>
        @endability

        @ability('Admin','View-Users,View-Roles,View-Permissions')
        <li @if($route == 'permissions.index' || $route == 'roles.index' || $route == 'users.index') class="active treeview" @else class="treeview" @endif>
          <a href="#">
            <i class="fa fa-group"></i> <span>User Management</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @ability('Admin','View-Users')
            <li @if($route == 'users.index') class="active" @else @endif><a href="{{route('users.index')}}"><i class="fa fa-circle-o"></i> Users</a></li>
            @endability

            @ability('Admin','View-Roles')
            <li @if($route == 'roles.index') class="active" @else @endif><a href="{{route('roles.index')}}"><i class="fa fa-circle-o"></i> Role</a></li>
            @endability

            @ability('Admin','View-Permissions')
            <li @if($route == 'permissions.index') class="active" @else @endif><a href="{{route('permissions.index')}}"><i class="fa fa-circle-o"></i> Permission</a></li>
            @endability
          </ul>
        </li>
        @endability
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>