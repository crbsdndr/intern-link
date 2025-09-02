@extends('layouts.app')

@section('title', $title)

@section('content')
<h1>{{ $title }}</h1>
<div class="table-responsive">
<table class="table" id="data-table">
    <thead><tr></tr></thead>
    <tbody></tbody>
</table>
</div>
<p id="pagination-info" class="mt-2"></p>
<div class="d-flex gap-2">
    <button class="btn btn-secondary" id="back-btn">Back</button>
    <button class="btn btn-secondary" id="next-btn">Next</button>
</div>
<script>
const endpoint = "{{ $endpoint }}";
const tableHead = document.querySelector('#data-table thead tr');
const tableBody = document.querySelector('#data-table tbody');
const info = document.getElementById('pagination-info');
const backBtn = document.getElementById('back-btn');
const nextBtn = document.getElementById('next-btn');
const params = new URLSearchParams(window.location.search);
let currentPage = parseInt(params.get('page') || '1', 10);
if (isNaN(currentPage) || currentPage < 1) currentPage = 1;
function load(page){
    fetch(`${endpoint}?page=${page}&limit=10`)
        .then(r => r.json())
        .then(res => {
            tableHead.innerHTML = '';
            tableBody.innerHTML = '';
            const data = res.data || [];
            if (data.length) {
                Object.keys(data[0]).forEach(k => {
                    const th = document.createElement('th');
                    th.textContent = k;
                    tableHead.appendChild(th);
                });
                data.forEach(item => {
                    const tr = document.createElement('tr');
                    Object.values(item).forEach(v => {
                        const td = document.createElement('td');
                        td.textContent = v;
                        tr.appendChild(td);
                    });
                    tableBody.appendChild(tr);
                });
            } else {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 1;
                td.textContent = 'No data found.';
                tr.appendChild(td);
                tableBody.appendChild(tr);
            }
            const p = res.pagination;
            info.textContent = `${p.shown} out of ${p.total}`;
            currentPage = p.page;
            params.set('page', currentPage);
            history.replaceState(null, '', '?' + params.toString());
            backBtn.disabled = currentPage <= 1 || p.total === 0;
            nextBtn.disabled = currentPage >= p.total_pages || p.total === 0;
        });
}
backBtn.addEventListener('click', () => { if (currentPage > 1) load(currentPage - 1); });
nextBtn.addEventListener('click', () => { load(currentPage + 1); });
load(currentPage);
</script>
@endsection
