const form = document.getElementById('preform_form');
const form_select_ozon = document.getElementById('preform_form_ozon');
const form_select_ozon_type = document.getElementById('preform_form_type');

const updateForm = async (data, url, method) =>
{

    const req = await fetch(url, {
        method: method,
        body: data,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'charset': 'utf-8'
        }
    });

    return await req.text();

};


const parseTextToHtml = (text) =>
{
    const parser = new DOMParser();
    const html = parser.parseFromString(text, 'text/html');

    return html;
};

const changeOptions = async (event) =>
{
    document.getElementById('preform_form_type_select2').classList.add('disabled');

    let formData = new FormData();
    formData.delete(form.name + '[_token]');
    formData.append(event.target.getAttribute('name'), event.target.value);

    const updateFormResponse = await updateForm(formData, form.getAttribute('action'), form.getAttribute('method'));

    const html = parseTextToHtml(updateFormResponse);

    const new_form_select_type = html.getElementById('preform_form_type');

    form_select_ozon_type.removeAttribute('disabled');

    form_select_ozon_type.innerHTML = new_form_select_type.innerHTML;

    if(form_select_ozon_type.tagName === 'SELECT')
    {
        new NiceSelect(document.getElementById(new_form_select_type.getAttribute('id')), {
            searchable: true,
            id: 'select2-' + new_form_select_type.getAttribute('id')
        });
    }

    document.getElementById('preform_form_type_select2').classList.remove('disabled');
};

form_select_ozon.addEventListener('change', (e) => changeOptions(e));
