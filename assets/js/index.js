var nameEl = document.getElementById('picker');
var nameDiv = document.getElementById('picker_div');
var nameVl = document.getElementById('area_value');

var first = []; /* 省，直辖市 */
var second = []; /* 市 */
var third = []; /* 镇 */
var area = [];

var selectedIndex = [0, 0, 0, 0]; /* 默认选中的地区 */
var selected_parent = [0, 1, 2, 3] /* 默认选中的地区的数据库ID */
if(typeof selectedIndex_old!="undefined"){
  selectedIndex = selectedIndex_old;
}
if(typeof selected_parent_old!="undefined"){
  selected_parent = selected_parent_old;
}
var checked = [0, 0, 0, 0]; /* 已选选项 */
$.ajaxSettings.async = false;
function creatList(parent_id, list){
  $.getJSON('/wx_api/get_region?parent_id=' + parent_id, function(data){
    if (data.status == 1) {
      data.result.forEach(function(item, index){
        var temp = new Object();
        temp.text = item.name;
        temp.value = item.id;
        list.push(temp);
    })
  }
  })
}

creatList(selected_parent[0], first);
creatList(selected_parent[1], second);
creatList(selected_parent[2], third);
creatList(selected_parent[3], area);


var picker = new Picker({
	data: [first, second, third, area],
  selectedIndex: selectedIndex,
	title: '地址选择'
});

picker.on('picker.select', function (selectedVal, selectedI) {
  var text1 = first[selectedI[0]].text;
  var text2 = second[selectedI[1]].text;
  var text3 = third[selectedI[2]] ? third[selectedI[2]].text : '';
  var text4 = area[selectedI[3]] ? area[selectedI[3]].text : '';

  var val1 = selectedVal[0];
  var val2 = selectedVal[1];
  var val3 = selectedVal[2] ? selectedVal[2] : '0';
  var val4 = selectedVal[3] ? selectedVal[3] : '0';

  $.ajaxSettings.async = false;
  //检查地址是否满足要求
  $.post('/wx_api/check_region',{val1:val1, val2:val2, val3:val3, val4:val4}, function(data){
    var return_ = JSON.parse(data)
    if(return_.status != 1){
      var index_arr = return_.result.index_arr;
      var value_arr = return_.result.value_arr;
      selectedIndex = [index_arr.index_1, index_arr.index_2, index_arr.index_3, index_arr.index_4]; /* 默认选中的地区 */
      selected_parent = [value_arr.province_p, value_arr.city_p, value_arr.district_p, value_arr.twon_p]; /* 默认选中的地区 */
      second = [];
      third = [];
      area = [];
      checked[0] = selectedIndex[0];
      creatList(first[checked[0]].value, second);
      checked[1] = selectedIndex[1];
      creatList(second[checked[1]].value, third);
      checked[2] = selectedIndex[2];
      creatList(third[checked[2]].value, area);
      picker.refillColumn(0, first);
      picker.refillColumn(1, second);
      picker.refillColumn(2, third);
      picker.refillColumn(3, area);
      picker.scrollColumn(0, selectedIndex[0]);
      picker.scrollColumn(1, selectedIndex[1]);
      picker.scrollColumn(2, selectedIndex[2]);
      picker.scrollColumn(3, selectedIndex[3]);
      console.log(selectedIndex[2]);
      layer.msg('操作太快,请重新确认地址!');
    }else{
      nameEl.innerHTML = text1 + ' ' + text2 + ' ' + text3 + ' ' + text4;
      nameVl.value = val1 + ',' + val2 + ',' + val3 + ',' + val4;
    }
  });
});

picker.on('picker.change', function (index, selectedIndex) {

  if (index === 0){
    firstChange();
  } else if (index === 1) {
    secondChange();
  } else if (index === 2){
    thirdChange();
  }

  function firstChange() {
    var index = layer.load(1, {shade: [0.1,'#fff']});
    second = [];
    third = [];
    area = [];
    checked[0] = selectedIndex;
    creatList(first[checked[0]].value, second);
    checked[1] = 0;
    creatList(second[0].value, third);
    checked[2] = 0;
    creatList(third[0].value, area);

    picker.refillColumn(1, second);
    picker.refillColumn(2, third);
    picker.refillColumn(3, area);
    picker.scrollColumn(1, 0);
    picker.scrollColumn(2, 0);
    picker.scrollColumn(3, 0)
    layer.close(index);
  }

  function secondChange() {
    var index = layer.load(1, {shade: [0.1,'#fff']});
    third = [];
    area = [];
    checked[1] = selectedIndex;
    creatList(second[checked[1]].value, third);
    checked[2] = 0;
    creatList(third[0].value, area);
    picker.refillColumn(2, third);
    picker.refillColumn(3, area);
    picker.scrollColumn(2, 0);
    picker.scrollColumn(3, 0)
    layer.close(index);
  }

  function thirdChange() {
    var index = layer.load(1, {shade: [0.1,'#fff']});
    area = [];
    checked[2] = selectedIndex;
    creatList(third[checked[2]].value, area);
    picker.refillColumn(3, area);
    picker.scrollColumn(3, 0)
    layer.close(index);
  }


});

picker.on('picker.valuechange', function (selectedVal, selectedI) {
  var val1 = selectedVal[0];
  var val2 = selectedVal[1];
  var val3 = selectedVal[2] ? selectedVal[2] : '0';
  var val4 = selectedVal[3] ? selectedVal[3] : '0';
  $.ajaxSettings.async = false;
  //检查地址是否满足要求
  $.post('/wx_api/check_region',{val1:val1, val2:val2, val3:val3, val4:val4}, function(data){
    var return_ = JSON.parse(data)
    if(return_.status != 1){
      var index_arr = return_.result.index_arr;
      var value_arr = return_.result.value_arr;
      selectedIndex = [index_arr.index_1, index_arr.index_2, index_arr.index_3, index_arr.index_4]; /* 默认选中的地区 */
      selected_parent = [value_arr.province, value_arr.city, value_arr.district, value_arr.twon]; /* 默认选中的地区 */
      second = [];
      third = [];
      area = [];
      checked[0] = selectedIndex[0];
      creatList(first[checked[0]].value, second);
      checked[1] = selectedIndex[1];
      creatList(second[checked[1]].value, third);
      checked[2] = selectedIndex[2];
      creatList(third[checked[2]].value, area);
      picker.refillColumn(0, first);
      picker.refillColumn(1, second);
      picker.refillColumn(2, third);
      picker.refillColumn(3, area);
      picker.scrollColumn(0, selectedIndex[0]);
      picker.scrollColumn(1, selectedIndex[1]);
      picker.scrollColumn(2, selectedIndex[2]);
      picker.scrollColumn(3, selectedIndex[3]);
      console.log(selectedIndex[2]);
    }
  });
   console.log(selectedVal);
   console.log(selectedI);
});

nameDiv.addEventListener('click', function () {
	picker.show();
});



