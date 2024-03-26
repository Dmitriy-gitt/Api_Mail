ymaps3.ready.then(init);

// Инициализация и уничтожение карты при нажатии на кнопку.
async function init () {
    let myMap;

    $('#toggle').bind({
        click: async function () {
            if (!myMap) {
                const {YMap, YMapDefaultSchemeLayer, YMapDefaultFeaturesLayer} = ymaps3;
                const {YMapDefaultMarker} = await ymaps3.import('@yandex/ymaps3-markers@0.0.1'); // Импортируем пакет, что бы добавит маркер по умолчанию
                myMap = new YMap(document.getElementById('map'), {
                    location:{
                        zoom: 9,
                        center: [55.010251, 82.958437], // Новосибирск
                        }
                });

                myMap.addChild(new YMapDefaultSchemeLayer()); // добавляет на карту источник данных и слой схемы карт
                myMap.addChild(new YMapDefaultFeaturesLayer()); // добавляет на карту источник данных и слой геообъектов (полигоны, линии, точки, метки);
                
                for (let i = 0; i < pointArr.length; i++) {
                    let point = pointArr[i];
                    myMap.setLocation({center:[point.longitude, point.latitude], zoom: 7});
                    let marker = new YMapDefaultMarker({coordinates: [point.longitude, point.latitude], draggable: false});
                    myMap.addChild(marker);
                    console.log([point.latitude, point.longitude]);
                }
                
                $("#toggle").attr('value', 'Скрыть карту');
            }
            else {
                myMap.destroy();// Деструктор карты
                myMap = null;
                $("#toggle").attr('value', 'Показать карту снова');
            }
        }
    });
}

