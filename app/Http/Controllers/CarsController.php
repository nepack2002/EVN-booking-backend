<?php

namespace App\Http\Controllers;

use App\Imports\CarsImport;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CarsController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'ten_xe' => 'required|min:2',
            'mau_xe' => 'required',
            'user_id' => 'required',
            'bien_so_xe' => 'required|unique:cars,bien_so_xe',
            'so_khung' => 'required|unique:cars,so_khung',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required',
            'han_dang_kiem_tiep_theo' => 'required',
            'anh_xe' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'location' => 'required',
            'lat_location' => 'required',
            'long_location' => 'required',
        ]);

        if ($request->hasFile('anh_xe') && $request->file('anh_xe')->isValid()) {
            $file_name = $request->file('anh_xe')->getClientOriginalName();
            $request->file('anh_xe')->move(public_path('images'), $file_name);
            $file_path = 'images/' . $file_name;

            $car = new Car();
            $car->ten_xe = $request->ten_xe;
            $car->mau_xe = $request->mau_xe;
            $car->user_id = $request->user_id;
            $car->bien_so_xe = $request->bien_so_xe;
            $car->so_khung = $request->so_khung;
            $car->so_cho = $request->so_cho;
            $car->so_dau_xang_tieu_thu = $request->so_dau_xang_tieu_thu;
            $car->ngay_bao_duong_gan_nhat = $request->ngay_bao_duong_gan_nhat;
            $car->han_dang_kiem_tiep_theo = $request->han_dang_kiem_tiep_theo;
            $car->location = $request->location;
            $car->lat_location = $request->lat_location;
            $car->long_location = $request->long_location;
            $car->anh_xe = $file_path;

            $car->save();

            return response()->json(['message' => 'Thêm xe thành công'], 200);
        }
    }

    public function getCar(Request $request)
    {
        $query = $request->query('query');

        $cars = Car::with('user')->where('ten_xe', 'LIKE', '%' . $query . '%')->paginate(20);
        $carsCollection = $cars->items();
        $domain = config('app.url');
        collect($carsCollection)->map(function ($car) use ($domain) {
            // Thay đổi đường dẫn ảnh và tên người dùng
            if ($car->anh_xe) {
                $imagePath = $car->anh_xe;
                $imageUrl = asset($imagePath);
                $imageUrl = $domain . $imageUrl;
                $car->anh_xe = $imageUrl;
            }
            // Thay đổi trường user_id thành tên người dùng
            // $car->user_id = $car->user?$car->user->name:null;
            return $car;
        });

        return response()->json($cars);
    }

    public function get(string $id)
    {
        $car = Car::with('user')->where('id', $id)->first();
        $domain = config('app.url');
        $imagePath = $car->anh_xe;
        $imageUrl = asset($imagePath);
        $imageUrl = $domain . $imageUrl;
        $car->anh_xe_preview = $imageUrl;
        unset($car->anh_xe);
        return response()->json($car);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'ten_xe' => 'required|min:2',
            'mau_xe' => 'required',
            'user_id' => 'required',
            'bien_so_xe' => 'required|unique:cars,bien_so_xe,' . $id . ',id',
            'so_khung' => 'required|unique:cars,so_khung,' . $id . ',id',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required',
            'han_dang_kiem_tiep_theo' => 'required',
            'anh_xe' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'location' => 'required',
            'lat_location' => 'required',
            'long_location' => 'required',

        ]);

        $car = Car::find($id);
        $car->ten_xe = $request->ten_xe;
        $car->mau_xe = $request->mau_xe;
        $car->user_id = $request->user_id;
        $car->bien_so_xe = $request->bien_so_xe;
        $car->so_khung = $request->so_khung;
        $car->so_cho = $request->so_cho;
        $car->so_dau_xang_tieu_thu = $request->so_dau_xang_tieu_thu;
        $car->ngay_bao_duong_gan_nhat = $request->ngay_bao_duong_gan_nhat;
        $car->han_dang_kiem_tiep_theo = $request->han_dang_kiem_tiep_theo;
        $car->location = $request->location;
        $car->lat_location = $request->lat_location;
        $car->long_location = $request->long_location;

        if ($request->hasFile('anh_xe')) {
            $file_name = $request->file('anh_xe')->getClientOriginalName();
            $request->file('anh_xe')->move(public_path('images'), $file_name);
            $file_path = 'images/' . $file_name;
            $car->anh_xe = $file_path;
        }

        $car->save();

        return response()->json($id);
    }

    public function destroy(string $id)
    {
        $product = Car::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Xe đã được xoá thành công']);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = Excel::import(new CarsImport, $request->file('file'));

        return response()->json($data);
    }
}
