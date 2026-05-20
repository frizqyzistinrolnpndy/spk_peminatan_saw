from fastapi import FastAPI
from pydantic import BaseModel
import joblib
import numpy as np

# Inisialisasi aplikasi FastAPI
app = FastAPI(title="SPK Peminatan SMA ML API")

# Load model dan scaler yang sudah dilatih pada Pertemuan 8
model = joblib.load("model_peminatan.pkl")
scaler = joblib.load("scaler_peminatan.pkl")

# Struktur data input yang diterima dari PHP
class MapelInput(BaseModel):
    nama_mapel: str
    minat: float
    bakat: float
    nilai_rapor: float
    rencana_studi: float

@app.get("/")
def root():
    return {"message": "SPK Peminatan SMA ML API aktif"}

@app.post("/prediksi-batch")
def prediksi_batch(items: list[MapelInput]):
    hasil = []
    for item in items:
        # 1. Bentuk array input sesuai urutan kriteria training
        X = np.array([[item.minat, item.bakat, item.nilai_rapor, item.rencana_studi]])
        
        # 2. Skalakan data menggunakan scaler bawaan
        X_scaled = scaler.transform(X)
        
        # 3. Prediksi label (1 = Rekomen, 0 = Tidak)
        label = int(model.predict(X_scaled)[0])
        
        # 4. Ambil probabilitas khusus untuk kelas 1 (Rekomendasi) agar skor hybrid konsisten
        proba_rekomen = float(model.predict_proba(X_scaled)[0][1])
        
        hasil.append({
            "nama_mapel": item.nama_mapel,
            "label": label,
            "proba": round(proba_rekomen, 4),
            "keterangan": "Direkomendasikan" if label == 1 else "Tidak"
        })
    return {"hasil": hasil}