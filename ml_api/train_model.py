import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.preprocessing import StandardScaler
import joblib

# 1. Membaca dataset latih
data = pd.read_csv("dataset_peminatan.csv")

# 2. Memisahkan Fitur (X) sebagai input dan Label (y) sebagai output target
X = data[['minat', 'bakat', 'nilai_rapor', 'rencana_studi']]
y = data['label']

# 3. Scaling Fitur menggunakan StandardScaler agar rentang nilai setara
# (Menyamakan skala minat [1-5] dengan nilai rapor [1-100] agar AI tidak bias)
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# 4. Membagi data: 80% untuk pelatihan, 20% untuk pengujian
X_train, X_test, y_train, y_test = train_test_split(
    X_scaled, y, test_size=0.2, random_state=42
)

# 5. Inisialisasi dan melatih model Random Forest Classifier (Menggunakan 100 pohon keputusan)
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# 6. Evaluasi tingkat akurasi hasil prediksi model
y_pred = model.predict(X_test)
akurasi = accuracy_score(y_test, y_pred)
print(f"--- Evaluasi Model AI Pertemuan 8 ---")
print(f"Tingkat Akurasi Model: {akurasi * 100:.1f}%")
print("\nLaporan Klasifikasi Detail:")
print(classification_report(y_test, y_pred))

# 7. Menyimpan model matang (otak AI) dan scaler ke dalam format berkas .pkl
joblib.dump(model, 'model_peminatan.pkl')
joblib.dump(scaler, 'scaler_peminatan.pkl')
print("\n[INFO] Berkas model_peminatan.pkl & scaler_peminatan.pkl berhasil diekspor!")