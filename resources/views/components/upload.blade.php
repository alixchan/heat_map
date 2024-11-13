<div class="form-group mb-4">
    <label for=@yield('type') class="form-label"><i class="bi bi-upload"></i> Выберите входной файл</label>
    <div class="input-group mx-5">
    <input type="file" id="file" name=@yield('type') accept=".xls,.xlsx" class="form-control-file">
    </div>
    <small class="form-text text-muted">Допустимые форматы: .xls, .xlsx</small>
</div>