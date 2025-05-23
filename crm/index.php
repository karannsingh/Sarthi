<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
    <div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
      <?php
        require('include/header.php');
      ?>
      <div class="body flex-grow-1 px-3">
        <div class="container-lg">
          <div class="fs-2 fw-semibold">Dashboard</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
              <li class="breadcrumb-item">
                <!-- if breadcrumb is single--><span>Home</span>
              </li>
              <li class="breadcrumb-item active"><span>Dashboard</span></li>
            </ol>
          </nav>
          <div class="row">
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="card-body p-4">
                      <div class="row">
                        <div class="col-md-12">
                          <a class="btn btn-success text-white" href="TimeSheet.php">Go To Time Sheet</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- <div class="row">
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="card-body p-4">
                      <div class="row">
                        <div class="col">
                          <div class="card-title fs-4 fw-semibold">Sale</div>
                          <div class="card-subtitle text-disabled">January - July 2022</div>
                        </div>
                        <div class="col text-end text-primary fs-4 fw-semibold">$613.200</div>
                      </div>
                    </div>
                    <div class="chart-wrapper mt-3" style="height:150px;">
                      <canvas class="chart" id="card-chart-new1" height="75"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="card mb-4">
                    <div class="card-body">
                      <div class="d-flex justify-content-between">
                        <div class="card-title text-disabled">Customers</div>
                        <div class="bg-primary bg-opacity-25 text-primary p-2 rounded">
                          <svg class="icon icon-xl">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-people"></use>
                          </svg>
                        </div>
                      </div>
                      <div class="fs-4 fw-semibold pb-3">44.725</div><small class="text-danger">(-12.4%
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom"></use>
                        </svg>)</small>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="card mb-4">
                    <div class="card-body">
                      <div class="d-flex justify-content-between">
                        <div class="card-title text-disabled">Orders</div>
                        <div class="bg-primary bg-opacity-25 text-primary p-2 rounded">
                          <svg class="icon icon-xl">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-cart"></use>
                          </svg>
                        </div>
                      </div>
                      <div class="fs-4 fw-semibold pb-3">385</div><small class="text-success">(17.2%
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-arrow-top"></use>
                        </svg>)</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="card mb-4">
                <div class="card-body p-4">
                  <div class="card-title fs-4 fw-semibold">Traffic</div>
                  <div class="card-subtitle text-disabled">January 01, 2021 - December 31, 2021</div>
                  <div class="chart-wrapper" style="height:300px;margin-top:40px;">
                    <canvas class="chart" id="main-bar-chart" height="300"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-9">
              <div class="card mb-4">
                <div class="card-body p-4">
                  <div class="row">
                    <div class="col">
                      <div class="card-title fs-4 fw-semibold">Users</div>
                      <div class="card-subtitle text-disabled mb-4">1.232.150 registered users</div>
                    </div>
                    <div class="col-auto ms-auto">
                      <button class="btn btn-secondary">
                        <svg class="icon me-2">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user-plus"></use>
                        </svg>Add new user
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table mb-0">
                      <thead class="fw-semibold text-disabled">
                        <tr class="align-middle">
                          <th class="text-center">
                            <svg class="icon">
                              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-people"></use>
                            </svg>
                          </th>
                          <th>User</th>
                          <th class="text-center">Country</th>
                          <th>Usage</th>
                          <th>Activity</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/1.jpg" alt="user@email.com"><span class="avatar-status bg-success"></span></div>
                          </td>
                          <td>
                            <div>Yiorgos Avraamu</div>
                            <div class="small text-disabled"><span>New</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-us"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">50%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-success bg-opacity-15">
                              <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">10 sec ago</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/2.jpg" alt="user@email.com"><span class="avatar-status bg-danger-gradient"></span></div>
                          </td>
                          <td>
                            <div>Avram Tarasios</div>
                            <div class="small text-disabled"><span>Recurring</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-br"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">10%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-primary bg-opacity-15">
                              <div class="progress-bar bg-primary-gradient" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">5 minutes ago</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/3.jpg" alt="user@email.com"><span class="avatar-status bg-warning-gradient"></span></div>
                          </td>
                          <td>
                            <div>Quintin Ed</div>
                            <div class="small text-disabled"><span>New</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-in"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">74%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-warning bg-opacity-15">
                              <div class="progress-bar bg-warning-gradient" role="progressbar" style="width: 74%" aria-valuenow="74" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">1 hour ago</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/4.jpg" alt="user@email.com"><span class="avatar-status bg-secondary-gradient"></span></div>
                          </td>
                          <td>
                            <div>Enéas Kwadwo</div>
                            <div class="small text-disabled"><span>New</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-fr"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">98%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-danger bg-opacity-15">
                              <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 98%" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">Last month</div>
                          </td>
                          <td>
                            <div class="dropdown">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/5.jpg" alt="user@email.com"><span class="avatar-status bg-success"></span></div>
                          </td>
                          <td>
                            <div>Agapetus Tadeáš</div>
                            <div class="small text-disabled"><span>New</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-es"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">22%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-info bg-opacity-15">
                              <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">Last week</div>
                          </td>
                          <td>
                            <div class="dropdown dropup">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                        <tr class="align-middle">
                          <td class="text-center">
                            <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/avatars/6.jpg" alt="user@email.com"><span class="avatar-status bg-danger-gradient"></span></div>
                          </td>
                          <td>
                            <div>Friderik Dávid</div>
                            <div class="small text-disabled"><span>New</span> | Registered: Jan 1, 2020</div>
                          </td>
                          <td class="text-center">
                            <svg class="icon icon-xl">
                              <use xlink:href="vendors/@coreui/icons/svg/flag.svg#cif-pl"></use>
                            </svg>
                          </td>
                          <td>
                            <div class="d-flex justify-content-between mb-1">
                              <div class="fw-semibold">43%</div><small class="text-disabled">Jun 11, 2020 - Jul 10, 2020</small>
                            </div>
                            <div class="progress progress-thin bg-success bg-opacity-15">
                              <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                          </td>
                          <td>
                            <div class="small text-disabled">Last login</div>
                            <div class="fw-semibold">Yesterday</div>
                          </td>
                          <td>
                            <div class="dropdown dropup">
                              <button class="btn btn-transparent p-0 dark:text-high-emphasis" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="icon">
                                  <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                                </svg>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Info</a><a class="dropdown-item" href="#">Edit</a><a class="dropdown-item text-danger" href="#">Delete</a></div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card mb-4 text-white bg-primary-gradient">
                <div class="card-body p-4 pb-0 d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fs-4 fw-semibold">26K <span class="fs-6 fw-normal">(-12.4%
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom"></use>
                        </svg>)</span></div>
                    <div>Users</div>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <svg class="icon">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
                  </div>
                </div>
                <div class="chart-wrapper mt-3 mx-3" style="height:80px;">
                  <canvas class="chart" id="card-chart1" height="70"></canvas>
                </div>
              </div>
              <div class="card mb-4 text-white bg-warning-gradient">
                <div class="card-body p-4 pb-0 d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fs-4 fw-semibold">2.49% <span class="fs-6 fw-normal">(84.7%
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-arrow-top"></use>
                        </svg>)</span></div>
                    <div>Conversion Rate</div>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <svg class="icon">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
                  </div>
                </div>
                <div class="chart-wrapper mt-3" style="height:80px;">
                  <canvas class="chart" id="card-chart3" height="70"></canvas>
                </div>
              </div>
              <div class="card mb-4 text-white bg-danger-gradient">
                <div class="card-body p-4 pb-0 d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fs-4 fw-semibold">44K <span class="fs-6 fw-normal">(-23.6%
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom"></use>
                        </svg>)</span></div>
                    <div>Sessions</div>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <svg class="icon">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-options"></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div>
                  </div>
                </div>
                <div class="chart-wrapper mt-3 mx-3" style="height:80px;">
                  <canvas class="chart" id="card-chart4" height="70"></canvas>
                </div>
              </div>
            </div>
          </div> -->
          <!-- <div class="row">
            <div class="col-md-12">
              <div class="card mb-4">
                <div class="card-body p-4">
                  <div class="card-title fs-4 fw-semibold">Traffic</div>
                  <div class="card-subtitle text-disabled border-bottom mb-3 pb-4">Last Week</div>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="row">
                        <div class="col-6">
                          <div class="border-start border-start-4 border-start-info px-3 mb-3"><small class="text-disabled">New Clients</small>
                            <div class="fs-5 fw-semibold">9.123</div>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="border-start border-start-4 border-start-danger px-3 mb-3"><small class="text-disabled">Recuring Clients</small>
                            <div class="fs-5 fw-semibold">22.643</div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4 pt-4 border-top">
                        <div class="progress-group-prepend"><span class="text-disabled small">Monday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 34%" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Tuesday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 56%" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 94%" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Wednesday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 12%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 67%" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Thursday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 91%" aria-valuenow="91" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Friday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 22%" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 73%" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Saturday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 53%" aria-valuenow="53" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 82%" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4">
                        <div class="progress-group-prepend"><span class="text-disabled small">Sunday</span></div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-info-gradient" role="progressbar" style="width: 9%" aria-valuenow="9" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-danger-gradient" role="progressbar" style="width: 69%" aria-valuenow="69" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="row">
                        <div class="col-6">
                          <div class="border-start border-start-4 border-start-warning px-3 mb-3"><small class="text-disabled">Pageviews</small>
                            <div class="fs-5 fw-semibold">78.623</div>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="border-start border-start-4 border-start-success px-3 mb-3"><small class="text-disabled">Organic</small>
                            <div class="fs-5 fw-semibold">49.123</div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-4 pt-4 border-top">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                          </svg>
                          <div>Male</div>
                          <div class="ms-auto fw-semibold">43%</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-warning-gradient" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group mb-5">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user-female"></use>
                          </svg>
                          <div>Female</div>
                          <div class="ms-auto fw-semibold">37%</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-warning-gradient" role="progressbar" style="width: 43%" aria-valuenow="43" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-google"></use>
                          </svg>
                          <div>Organic Search</div>
                          <div class="ms-auto fw-semibold me-2">191.235</div>
                          <div class="text-disabled small">(56%)</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 56%" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-facebook-f"></use>
                          </svg>
                          <div>Facebook</div>
                          <div class="ms-auto fw-semibold me-2">51.223</div>
                          <div class="text-disabled small">(15%)</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-twitter"></use>
                          </svg>
                          <div>Twitter</div>
                          <div class="ms-auto fw-semibold me-2">37.564</div>
                          <div class="text-disabled small">(11%)</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 11%" aria-valuenow="11" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                      <div class="progress-group">
                        <div class="progress-group-header">
                          <svg class="icon icon-lg me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-linkedin"></use>
                          </svg>
                          <div>LinkedIn</div>
                          <div class="ms-auto fw-semibold me-2">27.319</div>
                          <div class="text-disabled small">(8%)</div>
                        </div>
                        <div class="progress-group-bars">
                          <div class="progress progress-thin">
                            <div class="progress-bar bg-success-gradient" role="progressbar" style="width: 8%" aria-valuenow="8" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> -->
        </div>
      </div>
      
    <?php
require('include/footer.php');
?>