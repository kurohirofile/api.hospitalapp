<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Fileupload;

/**
 * @OA\Tag(
 *     name="Patients",
 *     description="API Endpoints for Pasien",
 * )
 */

class PatientController extends Controller
{
 /**
 * Metode konstruktor untuk mendaftarkan middleware verifikasi JWT.
 * 
 * Konstruktor ini memastikan bahwa setiap permintaan ke metode dalam
 * controller ini harus melewati middleware 'jwt.verify' terlebih dahulu.
 * Middleware ini umumnya memeriksa kevalidan token JWT yang terlampir
 * pada header permintaan. Jika token tidak valid atau tidak ada, bisa
 * mengembalikan respons 401 Unauthorized.
 */
    public function __construct() {
        $this->middleware('jwt.verify');
    }

/**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth"
 * )
 */

/**
 * @OA\Get(
 *     path="/patient",
 *     tags={"Patients"},
 *     summary="Mengambil daftar semua pasien",
 *     security={{"bearerAuth": {}}},
 *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     @OA\Response(
 *         response=200,
 *         description="Daftar pasien berhasil diambil",
 *         @OA\JsonContent(
 *             example={"status": true, "patients": {{"id": 1, "name": "John Doe"}, {"id": 2, "name": "Jane Smith"}}}
 *         )
 *     )
 * )
 */


 public function index() {
    // Mengambil semua data pasien dari database
    $data['status'] = true;
    $data['patients'] = Patient::all();
    
    // Mengembalikan respons JSON yang berisi data yang telah diambil
    return response()->json(compact('data'));
}
    
/**
 * @OA\Post(
 *     path="/patient",
 *     tags={"Patients"},
 *     summary="Membuat data pasien baru",
 *     security={{"bearerAuth": {}}},
  *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             example={
 *                 "name": "John Doe",
 *                 "email": "john.doe@example.com",
 *                 "phone": "1234567890",
 *                 "age": 30,
 *                 "gender": "Male",
 *                 "sickness": "Flu"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Data pasien berhasil dibuat",
 *         @OA\JsonContent(
 *             example={"status": true, "msg": "Berhasil Dibuat"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validasi gagal",
 *         @OA\JsonContent(
 *             example={"status": "fail", "message": {"The name field is required.", "The email field is required."}}
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal membuat data pasien",
 *         @OA\JsonContent(
 *             example={"message": "Tidak dapat membuat data pasien"}
 *         )
 *     )
 * )
 */

public function store(Request $request)
{
    try {
        // Validasi data input menggunakan Validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:patients',
            'phone' => 'required|numeric',
            'age' => 'required|numeric',
            'gender' => 'required|string',
            'sickness' => 'required|string',
        ]);

        // Jika validasi gagal, kembalikan respons JSON dengan status 400
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()], 400);
        } else {
            // Jika data valid, simpan data pasien baru ke dalam database
            $data = $request->all();
            Patient::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'sickness' => $data['sickness'],
            ]);

            // Siapkan data respons untuk sukses
            $response = [
                'status' => true,
                'msg' => 'Berhasil Dibuat'
            ];

            // Kembalikan respons JSON dengan status 200
            return response()->json($response, 200);
        }
    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama proses penyimpanan
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan umum
        return response()->json(['message' => 'Tidak dapat membuat data pasien'], 500);
    }
}
    
/**
 * @OA\Get(
 *     path="/patient/{id}",
 *     tags={"Patients"},
 *     summary="Menampilkan detail pasien berdasarkan ID",
  *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID dari pasien yang ingin ditampilkan",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Data pasien berhasil ditemukan",
 *         @OA\JsonContent(
 *             example={"status": true, "patient": {"id": 1, "name": "John Doe", "email": "john@example.com", "phone": "1234567890", "age": 30, "gender": "male", "sickness": "Flu"}}
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Pasien tidak ditemukan",
 *         @OA\JsonContent(
 *             example={"message": "Pasien tidak ditemukan"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal menampilkan data pasien",
 *         @OA\JsonContent(
 *             example={"message": "Tidak dapat menampilkan data pasien"}
 *         )
 *     )
 * )
 */

public function show($id)
{
    try {
        // Mengambil data pasien dari database berdasarkan ID
        $patient = Patient::where('id', $id)->first();

        // Memeriksa apakah data pasien ditemukan
        if ($patient != null) {
            // Jika data ditemukan, kembalikan respons JSON dengan status 200
            return response()->json(['status' => true, 'patient' => $patient], 200);
        } else {
            // Jika data tidak ditemukan, kembalikan respons JSON dengan pesan 'employee_not_found'
            return response()->json(['message' => 'Pasien tidak ditemukan'], 200);
        }
    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama proses pengambilan data
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan umum
        return response()->json(['message' => 'Tidak dapat menampilkan data pasien'], 500);
    }
}

/**
 * @OA\Put(
 *     path="/patient/{id}",
 *     tags={"Patients"},
 *     summary="Memperbarui data pasien berdasarkan ID",
  *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID dari pasien yang ingin diperbarui",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             example={
 *                 "name": "John Doe",
 *                 "email": "john@example.com",
 *                 "phone": "1234567890",
 *                 "age": 30,
 *                 "gender": "male",
 *                 "sickness": "Flu"
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Data pasien berhasil diperbarui",
 *         @OA\JsonContent(
 *             example={"status": true, "message": "Pasien berhasil diperbarui"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Pasien tidak ditemukan",
 *         @OA\JsonContent(
 *             example={"message": "Pasien tidak ditemukan"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal memperbarui data pasien",
 *         @OA\JsonContent(
 *             example={"message": "Tidak dapat memperbarui data pasien"}
 *         )
 *     )
 * )
 */

public function update(Request $request, $id)
{
    try {
        // Mengambil data pasien dari database berdasarkan ID
        $patient = Patient::where('id', $id)->first();

        // Memeriksa apakah data pasien ditemukan
        if ($patient != null) {
            // Jika ditemukan, ambil data dari Request dan lakukan pembaruan
            $data = $request->all();
            Patient::where('id', $id)->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'sickness' => $data['sickness'],
            ]);

            // Kembalikan respons JSON sukses dengan status 200
            return response()->json(['status' => true, 'message' => 'Pasien berhasil diperbarui'], 200);
        } else {
            // Jika pasien tidak ditemukan, kembalikan respons JSON dengan pesan 'Pasien tidak ditemukan'
            return response()->json(['message' => 'Pasien tidak ditemukan'], 200);
        }
    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama proses pembaruan data
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan umum
        return response()->json(['message' => 'Tidak dapat memperbarui data pasien'], 500);
    }
}

/**
 * @OA\Delete(
 *     path="/patient/{id}",
 *     tags={"Patients"},
 *     summary="Menghapus data pasien berdasarkan ID",
  *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID dari pasien yang ingin dihapus",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Data pasien berhasil dihapus",
 *         @OA\JsonContent(
 *             example={"status": true, "message": "Pasien berhasil dihapus"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Pasien tidak ditemukan",
 *         @OA\JsonContent(
 *             example={"message": "Pasien tidak ditemukan"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal menghapus data pasien",
 *         @OA\JsonContent(
 *             example={"message": "Tidak dapat menghapus data pasien"}
 *         )
 *     )
 * )
 */

public function destroy($id)
{
    try {
        // Mengambil data pasien dari database berdasarkan ID
        $patient = Patient::where('id', $id)->first();

        // Memeriksa apakah data pasien ditemukan
        if ($patient != null) {
            // Jika ditemukan, hapus data pasien dari database
            Patient::where('id', $id)->delete();

            // Kembalikan respons JSON sukses dengan status 200
            return response()->json(['status' => true, 'message' => 'Pasien berhasil dihapus'], 200);
        } else {
            // Jika pasien tidak ditemukan, kembalikan respons JSON dengan pesan 'Pasien tidak ditemukan'
            return response()->json(['message' => 'Pasien tidak ditemukan'], 200);
        }
    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama proses penghapusan data
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan umum
        return response()->json(['message' => 'Tidak dapat menghapus data pasien'], 500);
    }
}

/**
 * @OA\Post(
 *     path="/fileupload",
 *     tags={"Patients"},
   *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     security={{"bearerAuth": {}}},
 *     summary="Mengunggah file dan menyimpan data terkait",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="file",
 *                     description="File yang akan diunggah",
 *                     type="file"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     description="Nama terkait file (opsional)",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     description="Deskripsi terkait file (opsional)",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="File berhasil diunggah dan data berhasil disimpan",
 *         @OA\JsonContent(
 *             example={"status": true, "msg": "Berhasil dibuat"}
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validasi gagal",
 *         @OA\JsonContent(
 *             example={"status": "fail", "message": {"The file field is required."}}
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal membuat file",
 *         @OA\JsonContent(
 *             example={"message": "Gagal membuat file"}
 *         )
 *     )
 * )
 */

public function fileupload(Request $request)
{
    // Validasi input untuk memastikan bahwa file yang diunggah ada
    $validator = Validator::make($request->all(), [
        'file' => 'required'
    ]);

    // Jika validasi gagal, kembalikan respons JSON dengan status 400
    if ($validator->fails()) {
        return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()], 400);
    }

    // Jika validasi berhasil, lanjutkan dengan proses pengelolaan file dan data
    try {
        $data = $request->all();

        if (isset($data['file'])) {
            // Ambil file yang diunggah
            $file = $data['file'];

            // Hapus 'file' dari data untuk disimpan di tabel Fileupload
            unset($data['file']);

            // Upload file ke direktori 'uploads/patients' menggunakan method fileUpload()
            $data['name'] = FileUploadController::fileUpload($file, 'uploads/patients');
        }

        // Simpan data (tanpa file) ke dalam tabel Fileupload
        Fileupload::create($data);

        // Kembalikan respons JSON sukses dengan status 200
        return response()->json(['status' => true, 'msg' => 'Berhasil dibuat'], 200);

    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama proses unggah file dan penyimpanan data
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan
        return response()->json(['message' => 'Gagal membuat file'], 500);
    }
}

/**
 * @OA\Get(
 *     path="/fileupload",
 *     tags={"Patients"},
 *     summary="Mengambil daftar semua file yang diunggah",
  *     description="Endpoint ini memerlukan autentikasi dengan menggunakan Bearer Token. Harap sertakan token bearer dalam header 'Authorization' saat melakukan permintaan.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Daftar file berhasil diambil",
 *         @OA\JsonContent(
 *             example={
 *                 "data": {
 *                     "files": {
 *                         {
 *                             "id": 1,
 *                             "name": "file1.txt",
 *                             "size": 1024,
 *                             "created_at": "2024-06-28 12:00:00",
 *                             "updated_at": "2024-06-28 12:00:00"
 *                         },
 *                         {
 *                             "id": 2,
 *                             "name": "file2.pdf",
 *                             "size": 2048,
 *                             "created_at": "2024-06-28 13:00:00",
 *                             "updated_at": "2024-06-28 13:00:00"
 *                         }
 *                     }
 *                 }
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Gagal mengambil daftar file",
 *         @OA\JsonContent(
 *             example={"message": "Gagal mengambil daftar file"}
 *         )
 *     )
 * )
 */


public function filelist()
{
    try {
        // Mengambil semua entri file dari tabel Fileupload
        $files = Fileupload::all();

        // Menyusun data untuk respons JSON
        $data = [
            'files' => $files
        ];

        // Kembalikan respons JSON dengan daftar file
        return response()->json(compact('data'), 200);

    } catch (\Exception $e) {
        // Tangani kesalahan yang terjadi selama pengambilan data
        // Kembalikan respons JSON dengan status 500 dan pesan kesalahan
        return response()->json(['message' => 'Gagal mengambil daftar file'], 500);
    }
}


}
