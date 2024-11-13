<nav class="navbar navbar-dark sticky-top bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Тепловые карты</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>


<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasMenuLabel">Меню</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <h6 class="d-flex justify-content-between align-items-center px-3 mt-5 mb-5 text-white">
                <span>Аналитика</span>
        </h6>
        <ul class="nav flex-column">
            <!-- <li class="nav-item">
                <a class="nav-link link-light" href="#">
                    <i class="bi bi-card-checklist"></i> РТК
                </a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link link-light" href="/esis">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Актуальность ЕСИС
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/bs">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Непрерывность БС
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/br">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Непрерывность БР
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/import">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Импортонезависимость
                </a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link link-light" href="#">
                    <i class="bi bi-file-earmark-ppt"></i> Презентация
                </a>
            </li> -->
            <h6 class="d-flex justify-content-between align-items-center px-3 mt-5 mb-5 text-white">
                <span>Загрузка выгрузок</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/esis">
                    <i class="bi bi-file-code"></i> Загрузить ЕСИС
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/esis_full">
                    <i class="bi bi-file-code"></i> Загрузить ЕСИС <small>full</small>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/sw">
                    <i class="bi bi-file-code"></i> Загрузить SW
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/dns">
                    <i class="bi bi-file-code"></i> Загрузить DNS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/kt670">
                    <i class="bi bi-file-code"></i> Загрузить КТ-670
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-light" href="/upload/cmdb">
                    <i class="bi bi-file-code"></i> Загрузить CMDB
                </a>
            </li>
        </ul>
    </div>
</div>