<?php

namespace App\Http\Controllers;

use App\Imports\CarsImport;
use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CarsController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user_id) {
            $user = User::with('car')->find($request->user_id);
            if ($user->car) {
                return response()->json(['messages' => 'Người dùng này đã sở hữu một xe. Không thể thêm xe mới.'], 409);
            }
        }
        $request->validate([
            'ten_xe' => 'required|min:2',
            'mau_xe' => 'required',
            'user_id' => 'required',
            'bien_so_xe' => 'required|unique:cars,bien_so_xe',
            'so_khung' => 'required|unique:cars,so_khung',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required|date_format:d/m/Y',
            'han_dang_kiem_tiep_theo' => 'required|date_format:d/m/Y',
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
            $car->ngay_bao_duong_gan_nhat = Carbon::createFromFormat('d/m/Y',$request->ngay_bao_duong_gan_nhat)->format('Y-m-d');
            $car->han_dang_kiem_tiep_theo = Carbon::createFromFormat('d/m/Y',$request->han_dang_kiem_tiep_theo)->format('Y-m-d');
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
        if (!empty($query)) {
            $cars = Car::with('user')->where('ten_xe', 'LIKE', '%' . $query . '%')->paginate(5);
            $carsCollection = $cars->items();
            $domain = config('app.url');
            $carsCollection = collect($carsCollection)->map(function ($car) use ($domain) {
                // Thay đổi đường dẫn ảnh và tên người dùng
                $imagePath = $car->anh_xe;
                $imageUrl = asset($imagePath);
                $imageUrl = $domain . $imageUrl;
                $car->anh_xe = $imageUrl;
                // Thay đổi trường user_id thành tên người dùng
                $car->user_id = $car->user->name;
                return $car;
            });
        } else {
            $cars = Car::with('user')->paginate(5);

            $carsCollection = $cars->items();
            $domain = config('app.url');
            $carsCollection = collect($carsCollection)->map(function ($car) use ($domain) {
                // Thay đổi đường dẫn ảnh và tên người dùng
                $imagePath = $car->anh_xe;
                $imageUrl = asset($imagePath);
                $imageUrl = $domain . $imageUrl;
                $car->anh_xe = $imageUrl;
                // Thay đổi trường user_id thành tên người dùng
                $car->user_id = $car->user->name;
                return $car;
            });
        }
        // Trả về phản hồi JSON với thông tin phân trang
        return response()->json($cars);
    }

    public function get(string $id)
    {
        $car = Car::find($id);
        $domain = config('app.url');
        $imagePath = $car->anh_xe;
        $imageUrl = asset($imagePath);
        $imageUrl = $domain . $imageUrl;
        $car->anh_xe = $imageUrl;
        return response()->json($car);
    }

    public function update(Request $request, string $id)
    {
        $existingCar = Car::where('user_id', $request->user_id)->where('id', '!=', $id)->first();
        if ($existingCar) {
            return response()->json(['messages' => 'Người dùng này đã sở hữu một xe khác. Không thể cập nhật xe mới với user_id này.'], 409);
        }
        $request->validate([
            'ten_xe' => 'required|min:2',
            'mau_xe' => 'required',
            'user_id' => 'required',
            'bien_so_xe' => 'required|unique:cars,bien_so_xe,' . $id . ',id',
            'so_khung' => 'required|unique:cars,so_khung,' . $id . ',id',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required|date_format:d/m/Y',
            'han_dang_kiem_tiep_theo' => 'required|date_format:d/m/Y',
            'anh_xe' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
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
        $car->ngay_bao_duong_gan_nhat = Carbon::parse($request->ngay_bao_duong_gan_nhat)->format('Y-m-d');
        $car->han_dang_kiem_tiep_theo = Carbon::parse($request->han_dang_kiem_tiep_theo)->format('Y-m-d');
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
        $request->validate([
            'file' => 'required|file',
        ]);

        $path = $request->file('file')->getRealPath();
        $data = Excel::import(new CarsImport, $path);

        return response()->json($data);
    }
}
