@extends('layouts.baseContent')

@section('main')

<main class="w-100 px-5">
    @include('partials._breadcrumbs', [
        'crumbs' => [
                ['página inicial', '/inicio']
            ]
    ])

    @include('partials._pageTitle', ['title' => 'Consultar Unidades Curriculares'])

    <section class="mt-5 mb-4 p-0 d-flex flex-column gap-4">
        <div class= "d-flex gap-4 align-items-stretch flex-wrap" >
            
            <select class="" name="ano_semestre" id="ano_semestre" aria-label="Filtre por ano e semestre">
                <option value="" selected>Ano Letivo - Semestre</option> 
                <option value="2023/24_2º semestre">2023/24 - 2º semestre</option>
                <option value="2023/24_1º semestre">2023/24 - 1º semestre</option>
                <option value="2022/23_2º semestre">2022/23 - 2º semestre</option>
                <option value="2022/23_1º semestre">2022/23 - 1º semestre</option>
                <option value="2021/22_2º semestre">2021/22 - 2º semestre</option>
                <option value="2021/22_1º semestre">2021/22 - 1º semestre</option>
            </select>
            
            <select class="" name="ciclo" id="ciclo" aria-label="Filtre por ciclo" >
                <option value="" selected>Ciclo</option> 
                <option value="Licenciatura">Licenciatura</option>
                <option value="Mestrado">Mestrado</option>
                <option value="Doutoramento">Doutoramento</option>
                <option value="Ctesp">Ctesp</option>
            </select>


            <div class="paco-searchbox">
                <input type="text" name="uc" id="uc" aria-label="Filtre por código ou nome de uc">
                <div><i class="fa-solid fa-search"></i></div>
            </div>

            <div class="paco-checkbox">
                <label for="my-classes-check">As minhas UC's</label>
                <input type="checkbox" name="my_classes" id="my-classes-check">
            </div>

        </div>
    </section>
    

    <table class="w-100 title-separator" id="table-ucs">
            <thead>
                <tr> 
                    <th scope="col"></th>
                    <th scope="col" >Cód</th>
                    <th scope="col">Dep</th>
                    <th scope="col">Nome</th>
                    <th scope="col" >Docente Responsável</th>
                </tr>
            </thead>
            <tbody>
                <tr data-id='91998'>
                    <th scope="row"></th>
                    <td>91998</td>
                    <td>DMAT</td>
                    <td>Cálculo I</td>
                    <td>Luísa Mendes</td>
                    <td></td>
                </tr>
                <tr data-id="8765">
                    <th scope="row"></th>
                    <td>8765</td>
                    <td>DECA</td>
                    <td>Web design</td>
                    <td>Rita Santos</td>
                    <td></td>
                </tr>
                <tr data-id="85095">
                    <th scope="row"></th>
                    <td>85095</td>
                    <td>DETI</td>
                    <td>Inteligência Artigicial</td>
                    <td>José Silva</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

</main>
@endsection