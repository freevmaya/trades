var utils = new (function() {
    this.minmaxCalc = (list, pmin=1, pmax=2)=>{
        var minmax = [1000000000, 0]; 
        for (var i=0; i<list.length; i++) {
            minmax[0] = Math.min(Math.min(list[i][pmin], list[i][pmax]), minmax[0]);
            minmax[1] = Math.max(Math.max(list[i][pmin], list[i][pmax]), minmax[1]);
        }
        return minmax;
    } 

    this.arrToFloat = (list, fields)=>{
        for (var i=0; i<list.length; i++)
            for (var f=0; f<fields.length; f++)
                list[i][fields[f]] = parseFloat(list[i][fields[f]]);
        return list;
    }

    this.strToFloat = (str, maxVal)=>{
        var f = parseFloat(str);
        if (str[str.length - 1] == '%') return f * maxVal / 100;
        else return f;
    }

    this.objToFloat = (obj, fields)=>{
        for (var f=0; f<fields.length; f++)
            obj[fields[f]] = parseFloat(obj[fields[f]]);
        return obj;
    }

    this.fillPairs = (pairsList, sel_pair)=>{
        var pairs = external.pairs;
        for (var i=0; i<pairs.length;i++) {
            var opt = $('<option value="' + pairs[i] + '">' + pairs[i] + '</option>');
            if (sel_pair == pairs[i]) opt.prop('selected', 'true');
            pairsList.append(opt);
        }            
    }

    this.fillDlg = (dlg, item)=>{
        if (item) {
            for (var n in item) {
                var v = item[n];
                var ctrl = dlg.find('[name="' + n + '"]');
                if (ctrl.length > 0) {
                    if ($.type(v) == 'object')
                        ctrl.val(JSON.stringify(v));
                    else ctrl.val(v);
                } else {
                    dlg.find('[name="' + n + '[]"]').each(function(i, ctrl) {
                        ctrl = $(ctrl);
                        if (ctrl.val() == v) ctrl.attr('checked', 1);
                    });
                }
            }
        }
    }

    this.arr2Avg = (elmt, idx2=0)=>{
        var sum = 0;
        for( var i = 0; i < elmt.length; i++ ) sum += elmt[i][idx2];
        return sum/elmt.length;
    }
})();