/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

executeFunc(function init()
{
    /* кнопка Добавить ФОТО */
    let $addImageButton = document.getElementById('photo_addCollection');

    /* Блок для новой коллекции */
    let $blockCollectionPhoto = document.getElementById('photo_collection');

    if($addImageButton === null)
    {
        return false;
    }

    /* удаление блока из коллекции изображений */
    let $delItemPhoto = $blockCollectionPhoto.querySelectorAll('.del-item-photo');

    $delItemPhoto.forEach(function(item)
    {
        item.addEventListener('click', function()
        {

            let $counter = $blockCollectionPhoto.getElementsByClassName('item-collection-photo').length;

            if($counter > 0)
            {
                item.closest('.item-collection-photo').remove();
            }


            let photo_collection = document.getElementById('photo_collection');


            let isRoot = false;

            photo_collection.querySelectorAll('.change-root').forEach(function(rootCheck)
            {
                if(rootCheck.checked === true)
                {
                    isRoot = true;
                }
            });


            if(isRoot === false)
            {
                photo_collection.querySelectorAll('.change-root').forEach(function(rootCheck, i)
                {
                    if(i === 0)
                    {
                        rootCheck.checked = true;
                        return true;
                    }
                });
            }
        });
    });

    /* изменение чекбокса*/
    let $changeRootPhoto = $blockCollectionPhoto.querySelectorAll('.change-root');

    $changeRootPhoto.forEach(function(item)
    {

        if($changeRootPhoto.length === 1 && item.checked === false)
        {
            item.checked = true;
        }

        item.addEventListener('change', function()
        {

            let photo_collection = document.getElementById('photo_collection');

            photo_collection.querySelectorAll('.change-root').forEach(function(rootCheck)
            {
                rootCheck.checked = false;
            });

            this.checked = true;
        });

    });

    let uploadedFile = $blockCollectionPhoto.querySelectorAll('input[type="file"]');

    uploadedFile.forEach(function(item)
    {

        item.addEventListener('change', function(e)
        {

            let newFile = item.files[0];
            let reader = new FileReader();
            // let image = item.parentNode.parentNode;
            let image = item.closest('.image-input');

            reader.onloadend = function()
            {
                image.style.setProperty("background-image", "url(" + reader.result + ")", "important")
            }

            if(newFile)
            {
                reader.readAsDataURL(newFile);
            } else
            {
                image.style.setProperty("background-image", "url(/img/blank.svg)", "important")
            }
        });
    });

    /* Добавляем новую коллекцию */
    $addImageButton.addEventListener('click', function()
    {

        let $addImageButton = this;

        /* получаем прототип коллекции  */
        let newPrototype = document.getElementById($addImageButton.dataset.prototype).dataset.prototype;
        let index = $addImageButton.dataset.index * 1;

        if(index === 8)
        {
            return;
        }

        /* Замена '__name__' в HTML-коде прототипа число, основанное на том, сколько коллекций */
        newPrototype = newPrototype.replace(/__images__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.classList.add('item-collection-photo')
        div.innerHTML = newPrototype;
        $blockCollectionPhoto.append(div);

        /* Удаляем при клике коллекцию СЕКЦИЙ */
        div.querySelector('.del-item-photo').addEventListener('click', function()
        {
            let $counter = $blockCollectionPhoto.getElementsByClassName('item-collection-photo').length;
            this.closest('.item-collection-photo').remove();
            let index = $addImageButton.dataset.index * 1;
            $addImageButton.dataset.index = (index - 1).toString()
        });

        let images = photo_collection.querySelectorAll('.change-root');

        if(images.length === 1)
        {
            let photo_collection = document.getElementById('photo_collection');

            photo_collection.querySelectorAll('.change-root').forEach(function(rootChack, i, arr)
            {
                rootChack.checked = true;
            });
        }

        div.querySelector('.change-root').addEventListener('change', function(selector)
        {

            let photo_collection = document.getElementById('photo_collection');

            photo_collection.querySelectorAll('.change-root').forEach(function(rootChack, i, arr)
            {
                rootChack.checked = false;
            });

            this.checked = true;
        });

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        $addImageButton.dataset.index = (index + 1).toString();

        /* загрузка изображения */
        let inputElement = div.querySelector('input[type="file"]');

        inputElement.addEventListener('change', function(e)
        {

            let file = inputElement.files[0];
            let reader = new FileReader();
            let image = div.querySelector('.image-input');

            reader.onloadend = function()
            {

                image.style.setProperty("background-image", "url(" + reader.result + ")", "important")
            }

            if(file)
            {
                reader.readAsDataURL(file);
            } else
            {
                image.style.setProperty("background-image", "url(/img/blank.svg)", "important")
            }
        });
    });

    return true;

});
