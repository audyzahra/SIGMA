import '../models/citizen_models.dart';

class CitizenDummyData {
  static const profile = CitizenProfile(name: 'Budi Pratama', email: 'budi.pratama@warga.id', location: 'Dusun 3 Cikedung, Indramayu');
  static const weather = {'condition': 'Cerah Berawan', 'temperature': '32°C', 'airQuality': 'Sedang (78)'};
  static const reports = [
    CitizenReport(id: 'REP-2026-0891', title: 'Kebakaran area hutan & lahan', status: 'Ditangani', time: '15 Sep 2026 • 14:20 WIB', location: 'Jl. Raya Cikedung Lor, Indramayu', progress: 3),
    CitizenReport(id: 'REP-2026-0894', title: 'Asap pekat di batas kebun', status: 'Menunggu', time: 'Hari ini • 16:05 WIB', location: 'Tersi, Indramayu, Jawa Barat', progress: 1),
    CitizenReport(id: 'REP-2026-0885', title: 'Titik api di semak belukar', status: 'Valid', time: '14 Sep 2026 • 11:15 WIB', location: 'Dusun Sukamaju, Tersi, Indramayu', progress: 2),
    CitizenReport(id: 'REP-2026-0870', title: 'Bongkahan kayu terbakar', status: 'Selesai', time: '12 Sep 2026 • 09:30 WIB', location: 'Jl. Poros Becikemur, Indramayu', progress: 4),
  ];
  static const safetyTips = [
    ('Gunakan Masker', 'Hindari area berasap tebal, kenakan masker pelindung.'),
    ('Hindari Area Kering', 'Jangan mendekati sumber api atau membakar sampah.'),
    ('Laporkan Temuan', 'Segera laporkan kepulan asap mencurigakan.'),
  ];
}
