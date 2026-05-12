@props([
    'provinceSelectId' => 'province-select',
    'regencySelectId' => 'regency-select',
    'provinceHiddenId' => 'province-hidden',
    'regencyHiddenId' => 'regency-hidden',
    'savedProvince' => null,
    'savedRegency' => null
])

    $(document).ready(function() {
        var provinceSelect = $('#{{ $provinceSelectId }}');
        var regencySelect = $('#{{ $regencySelectId }}');
        var provinceHidden = $('#{{ $provinceHiddenId }}');
        var regencyHidden = $('#{{ $regencyHiddenId }}');
        var savedProvince = @js($savedProvince);
        var savedRegency = @js($savedRegency);

        function loadRegencies(provinceCode, selectedRegencyName) {
            regencySelect.prop('disabled', false);
            $.get('/api/wilayah/regencies/' + provinceCode, function(res) {
                regencySelect.empty().append('<option></option>');
                (res.data || []).forEach(function(item) {
                    var isSelected = selectedRegencyName && item.name === selectedRegencyName;
                    var opt = new Option(item.name, item.code, isSelected, isSelected);
                    regencySelect.append(opt);
                });
                regencySelect.trigger('change');
                
                if (selectedRegencyName) {
                    regencyHidden.val(selectedRegencyName);
                }
            });
        }

        provinceSelect.on('select2:select', function(e) {
            var data = e.params.data;
            provinceHidden.val(data.text);
            regencySelect.val(null).trigger('change');
            regencyHidden.val('');
            loadRegencies(data.id);
        });

        regencySelect.on('select2:select', function(e) {
            regencyHidden.val(e.params.data.text);
        });

        // Initial Load Provinces
        $.get('/api/wilayah/provinces', function(res) {
            provinceSelect.empty().append('<option></option>');
            (res.data || []).forEach(function(item) {
                var isSelected = savedProvince && item.name === savedProvince;
                var opt = new Option(item.name, item.code, isSelected, isSelected);
                provinceSelect.append(opt);
                
                if (isSelected) {
                    loadRegencies(item.code, savedRegency);
                }
            });
            provinceSelect.trigger('change');
        });
    });
