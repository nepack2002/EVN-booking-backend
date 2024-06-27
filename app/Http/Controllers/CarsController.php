<?php

namespace App\Http\Controllers;

use App\Imports\CarsImport;
use App\Models\Car;
use App\Models\CarHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'so_may' => 'required|unique:cars,so_may',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required|date_format:d/m/Y',
            'han_dang_kiem_tiep_theo' => 'required|date_format:d/m/Y',
            'ngay_sua_chua_lon_gan_nhat' => 'required|date_format:d/m/Y',
            'anh_xe' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'location' => 'required',
            'lat_location' => 'required',
            'long_location' => 'required',
            'theo_doi_vi_tri' => 'required',
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
            $car->ngay_sua_chua_lon_gan_nhat = Carbon::createFromFormat('d/m/Y',$request->ngay_sua_chua_lon_gan_nhat)->format('Y-m-d');
            $car->location = $request->location;
            $car->lat_location = $request->lat_location;
            $car->long_location = $request->long_location;
            $car->theo_doi_vi_tri = $request->theo_doi_vi_tri;
            $car->anh_xe = $file_path;

            $car->save();

            return response()->json(['message' => 'Thêm xe thành công'], 200);
        }
    }

    public function getCar(Request $request)
    {
        $query = $request->query('query');

        $cars = Car::with('user','needVerify')->where('ten_xe', 'LIKE', '%' . $query . '%')->paginate(20);
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
            $car->user_id = $car->user?$car->user->name:null;
            return $car;
        });

        // Trả về phản hồi JSON với thông tin phân trang
        return response()->json($cars);
    }

    public function get(string $id)
    {
        $car = Car::with('history.user')->where('id', $id)->first();
        $domain = config('app.url');
        $imagePath = $car->anh_xe;
        $imageUrl = asset($imagePath);
        $imageUrl = $domain . $imageUrl;
        $car->anh_xe_preview = $imageUrl;
        unset($car->anh_xe);


        $car->ngay_bao_duong_gan_nhat = $car->ngay_bao_duong_gan_nhat ? \Illuminate\Support\Carbon::parse($car->ngay_bao_duong_gan_nhat)->format('d/m/Y') : null;
        $car->han_dang_kiem_tiep_theo = $car->han_dang_kiem_tiep_theo ? \Illuminate\Support\Carbon::parse($car->han_dang_kiem_tiep_theo)->format('d/m/Y') : null;
        $car->ngay_sua_chua_lon_gan_nhat = $car->ngay_sua_chua_lon_gan_nhat ? \Illuminate\Support\Carbon::parse($car->ngay_sua_chua_lon_gan_nhat)->format('d/m/Y') : null;


        foreach ($car->history as $item) {
            $item->ngay_bao_duong_gan_nhat = $item->ngay_bao_duong_gan_nhat ? Carbon::createFromFormat('Y-m-d', $item->ngay_bao_duong_gan_nhat)->format('d/m/Y') : null;
            $item->han_dang_kiem_tiep_theo = $item->han_dang_kiem_tiep_theo ? Carbon::createFromFormat('Y-m-d', $item->han_dang_kiem_tiep_theo)->format('d/m/Y') : null;
            $item->tai_lieu = $domain . asset($item->tai_lieu);
        }

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
            'so_may' => 'required|unique:cars,so_may,' . $id . ',id',
            'so_cho' => 'required',
            'so_dau_xang_tieu_thu' => 'required',
            'ngay_bao_duong_gan_nhat' => 'required|date_format:d/m/Y',
            'han_dang_kiem_tiep_theo' => 'required|date_format:d/m/Y',
            'ngay_sua_chua_lon_gan_nhat' => 'required|date_format:d/m/Y',
            'anh_xe' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'location' => 'required',
            'lat_location' => 'required',
            'long_location' => 'required',
            'theo_doi_vi_tri' => 'required',
        ]);

        $car = Car::find($id);
        $car->ten_xe = $request->ten_xe;
        $car->mau_xe = $request->mau_xe;
        $car->user_id = $request->user_id;
        $car->bien_so_xe = $request->bien_so_xe;
        $car->so_khung = $request->so_khung;
        $car->so_cho = $request->so_cho;
        $car->so_dau_xang_tieu_thu = $request->so_dau_xang_tieu_thu;
        $car->ngay_bao_duong_gan_nhat = Carbon::createFromFormat('d/m/Y',$request->ngay_bao_duong_gan_nhat)->format('Y-m-d');
        $car->han_dang_kiem_tiep_theo = Carbon::createFromFormat('d/m/Y',$request->han_dang_kiem_tiep_theo)->format('Y-m-d');
        $car->ngay_sua_chua_lon_gan_nhat = Carbon::createFromFormat('d/m/Y',$request->ngay_sua_chua_lon_gan_nhat)->format('Y-m-d');
        $car->location = $request->location;
        $car->lat_location = $request->lat_location;
        $car->long_location = $request->long_location;
        $car->theo_doi_vi_tri = $request->theo_doi_vi_tri;

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

    public function userEditCar(Request $request,string $id)
    {
        $validator = Validator::make($request->all(), [
            'hoa_don' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'ngay_bao_duong_gan_nhat' => 'date_format:d/m/Y',
            'han_dang_kiem_tiep_theo' => 'date_format:d/m/Y',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $car = Car::findOrFail($id);

        $carHistory = new CarHistory();
        $carHistory->car_id = $car->id;
        $carHistory->submit_by = Auth::user()->id;
        if (!empty($request->ngay_bao_duong_gan_nhat)) {
            $carHistory->ngay_bao_duong_gan_nhat = Carbon::createFromFormat('d/m/Y', $request->ngay_bao_duong_gan_nhat)->format('Y-m-d');
        }
        if (!empty($request->han_dang_kiem_tiep_theo)) {
            $carHistory->han_dang_kiem_tiep_theo = Carbon::createFromFormat('d/m/Y', $request->han_dang_kiem_tiep_theo)->format('Y-m-d');
        }
        if ($request->hasFile('hoa_don') && $request->file('hoa_don')->isValid()) {
            $file_name = $request->file('hoa_don')->getClientOriginalName();
            $request->file('hoa_don')->move(public_path('hoadon'), $file_name);
            $file_path = 'hoadon/' . $file_name;
            $carHistory->tai_lieu = $file_path;
        }
        $carHistory->save();

        return response()->json(['message' => 'Gửi yêu cầu chỉnh sửa thành công']);
    }

    public function allowChange(Request $request)
    {
        $historyId = $request->get('historyId');
        $history = CarHistory::findOrFail($historyId);

        $car = Car::findOrFail($history->car_id);
        if (!empty($history->ngay_bao_duong_gan_nhat)) {
            $car->ngay_bao_duong_gan_nhat = $history->ngay_bao_duong_gan_nhat;
        }

        if (!empty($history->han_dang_kiem_tiep_theo)) {
            $car->han_dang_kiem_tiep_theo = $history->han_dang_kiem_tiep_theo;
        }

        $history->trang_thai = '0';
        $history->save();
        $car->save();

        return response()->json([
            'message' => 'Phê duyệt thành công',
        ]);
    }
}
