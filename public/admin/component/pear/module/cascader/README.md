# Pear 级联选择器组件使用说明

## 组件介绍

这是一个基于 Layui 框架的级联选择器组件，用于选择多级数据（如省市区等层级数据）。组件支持静态数据和异步加载数据，提供搜索、清除等功能，适用于需要选择层级关系数据的场景。

## 基本使用方法

### 1. 引入组件

```javascript
layui.use(['jquery', 'cascader'], function () {
    var $ = layui.jquery;
    var cascader = layui.cascader;
    
    // 使用级联选择器
    // ...
});
```

### 2. 创建基本的级联选择器

```javascript
// HTML
<input type="text" id="cascaderDemo" placeholder="请选择">

// JavaScript
var cascaderIns = cascader.render({
    elem: '#cascaderDemo',
    data: [
        {
            value: '1',
            text: '选项1',
            children: [
                {value: '1-1', text: '选项1-1'},
                {value: '1-2', text: '选项1-2'}
            ]
        },
        {
            value: '2',
            text: '选项2',
            children: [
                {value: '2-1', text: '选项2-1'},
                {value: '2-2', text: '选项2-2'}
            ]
        }
    ],
    onChange: function(values, data) {
        console.log('选中的值:', values); // 例如: ['1', '1-1']
        console.log('选中的数据:', data); // 例如: {value: '1-1', text: '选项1-1'}
    }
});
```

## 配置参数

| 参数名 | 类型 | 默认值 | 说明 |
|-------|------|-------|------|
| elem | String/jQuery对象 | 无 | 目标元素选择器 |
| data | Array | null | 静态数据源，树形结构 |
| renderFormat | Function | `function(labels, values) { return labels.join(' / '); }` | 选择后展示的格式化函数 |
| clearable | Boolean | true | 是否支持清除选中项 |
| clearAllActive | Boolean | false | 是否清除所有选中项 |
| disabled | Boolean | false | 是否禁用组件 |
| trigger | String | 'click' | 次级菜单触发方式 ('click' 或 'hover') |
| changeOnSelect | Boolean | false | 是否点击每项选项值都改变 |
| reqData | Function | null | 异步获取数据的方法 |
| filterable | Boolean | false | 是否开启搜索功能 |
| notFoundText | String | '没有匹配数据' | 搜索列表为空时显示的内容 |
| reqSearch | Function | null | 自定义搜索的方法 |
| onChange | Function | null | 数据选择完成的回调函数 |
| onVisibleChange | Function | null | 展开和关闭弹窗时的回调函数 |
| itemHeight | Number | null | 下拉列表每一项的高度 |

## 实例方法

初始化级联选择器后会返回一个实例对象，可以调用以下方法：

### 1. open()
```javascript
cascaderIns.open(); // 展开下拉框
```

### 2. hide()
```javascript
cascaderIns.hide(); // 关闭下拉框
```

### 3. removeLoading()
```javascript
cascaderIns.removeLoading(); // 移除加载中的状态
```

### 4. setDisabled(dis)
```javascript
cascaderIns.setDisabled(true); // 禁用组件
cascaderIns.setDisabled(false); // 启用组件
```

### 5. getValue()
```javascript
var value = cascaderIns.getValue(); // 获取选中的值，格式为字符串，如 '1,1-1'
```

### 6. getLabel()
```javascript
var label = cascaderIns.getLabel(); // 获取显示的文本，如 '选项1 / 选项1-1'
```

### 7. setValue(value)
```javascript
cascaderIns.setValue('1,1-1'); // 设置选中的值
cascaderIns.setValue(''); // 清除选中的值
```

### 8. getData()
```javascript
var data = cascaderIns.getData(); // 获取原始数据源
```

## 高级用法

### 1. 异步加载数据

```javascript
cascader.render({
    elem: '#asyncCascader',
    reqData: function(values, callback, parentData) {
        // values: 已选中的值数组，如 ['1']
        // callback: 数据加载完成后的回调函数
        // parentData: 父级数据
        
        // 模拟异步请求
        setTimeout(function() {
            var data;
            if (!values || values.length === 0) {
                // 加载第一级数据
                data = [
                    {value: '1', text: '省份1', haveChildren: true},
                    {value: '2', text: '省份2', haveChildren: true}
                ];
            } else if (values.length === 1) {
                // 加载第二级数据
                data = [
                    {value: values[0] + '-1', text: '城市1', haveChildren: true},
                    {value: values[0] + '-2', text: '城市2', haveChildren: true}
                ];
            } else {
                // 加载第三级数据
                data = [
                    {value: values[1] + '-1', text: '区县1'},
                    {value: values[1] + '-2', text: '区县2'}
                ];
            }
            callback(data);
        }, 500);
    },
    onChange: function(values, data) {
        console.log('选中的值:', values);
        console.log('选中的数据:', data);
    }
});
```

### 2. 启用搜索功能

```javascript
cascader.render({
    elem: '#searchCascader',
    data: yourData, // 静态数据或异步加载的数据
    filterable: true, // 启用搜索功能
    reqSearch: function(keyword, callback, data) {
        // 自定义搜索逻辑
        // 这里可以实现后端搜索
        $.ajax({
            url: '/api/search',
            data: {keyword: keyword},
            success: function(res) {
                callback(res.data); // res.data 应该是 [{text: '...', value: '...'}] 格式
            }
        });
    },
    onChange: function(values, data) {
        console.log('选中的值:', values);
        console.log('选中的数据:', data);
    }
});
```

### 3. 自定义显示格式

```javascript
cascader.render({
    elem: '#customFormatCascader',
    data: yourData,
    renderFormat: function(labels, values) {
        // labels 是选中项的文本数组，如 ['选项1', '选项1-1']
        // values 是选中项的值数组，如 ['1', '1-1']
        return labels.join(' > '); // 自定义显示格式，如 '选项1 > 选项1-1'
    },
    onChange: function(values, data) {
        console.log('选中的值:', values);
        console.log('选中的数据:', data);
    }
});
```

### 4. 省市区数据处理辅助方法

组件提供了几个专门用于处理省市区数据的辅助方法：

```javascript
// 处理省市区数据，将 value 变为中文文本
var cityDataWithTextValue = cascader.getCityData(provinceCityAreaData);

// 处理省市区数据，不要区县级别
var cityDataWithoutArea = cascader.getCity(provinceCityAreaData);

// 处理省市区数据，只保留省份级别
var provinceOnlyData = cascader.getProvince(provinceCityAreaData);
```

## 数据格式说明

### 静态数据格式

```javascript
var data = [
    {
        value: '1', // 值
        text: '选项1', // 显示文本
        disabled: false, // 是否禁用（可选）
        haveChildren: true, // 是否有子节点（可选，会根据children自动判断）
        children: [ // 子节点数组
            {
                value: '1-1',
                text: '选项1-1',
                // 可以继续嵌套children
            }
        ]
    }
];
```

### 异步搜索结果格式

```javascript
var searchResult = [
    {
        text: '完整路径文本', // 如 '选项1 / 选项1-1'
        value: '1,1-1', // 对应的值，使用逗号分隔
        disabled: false // 是否禁用（可选）
    }
];
```

## 常见问题

1. **问题**：级联选择器无法显示
   **解决**：确保已正确引入 Layui 和级联选择器组件，并且在 DOM 元素加载完成后初始化组件

2. **问题**：异步加载数据不显示
   **解决**：检查 reqData 回调函数是否正确调用了 callback 并传入了正确格式的数据

3. **问题**：搜索功能不生效
   **解决**：确保设置了 filterable: true，并且数据格式正确

4. **问题**：下拉框位置不正确
   **解决**：组件内置了智能位置调整功能，会自动调整避免溢出屏幕，如果仍有问题可以检查页面布局