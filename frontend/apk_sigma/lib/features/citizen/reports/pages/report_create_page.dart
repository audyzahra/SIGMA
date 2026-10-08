import 'dart:typed_data';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';

import '../../data/datasources/citizen_remote_data_source.dart';
import '../../widgets/citizen_widgets.dart';

class ReportCreatePage extends StatefulWidget {
  const ReportCreatePage({
    super.key,
    required this.api,
  });

  final CitizenApi api;

  @override
  State<ReportCreatePage> createState() => _ReportCreatePageState();
}

class _ReportCreatePageState extends State<ReportCreatePage> {
  final _form = GlobalKey<FormState>();
  final _description = TextEditingController();
  final _picker = ImagePicker();
  final _mapController = MapController();

  String _type = 'fire';

  XFile? _photo;
  Uint8List? _photoBytes;

  LatLng? _selectedLocation;

  bool _sending = false;
  bool _loadingLocation = true;
  bool _locationDenied = false;

  static const LatLng _defaultLocation = LatLng(
    -6.9175,
    107.6191,
  );

  @override
  void initState() {
    super.initState();
    _initializeLocation();
  }

  @override
  void dispose() {
    _description.dispose();
    super.dispose();
  }

  Future<void> _initializeLocation() async {
    if (!mounted) return;

    setState(() {
      _loadingLocation = true;
      _locationDenied = false;
    });

    try {
      final serviceEnabled =
          await Geolocator.isLocationServiceEnabled();

      if (!serviceEnabled) {
        if (!mounted) return;

        setState(() {
          _loadingLocation = false;
          _locationDenied = true;
          _selectedLocation = _defaultLocation;
        });

        return;
      }

      var permission = await Geolocator.checkPermission();

      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        if (!mounted) return;

        setState(() {
          _loadingLocation = false;
          _locationDenied = true;
          _selectedLocation = _defaultLocation;
        });

        return;
      }

      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );

      final location = LatLng(
        position.latitude,
        position.longitude,
      );

      if (!mounted) return;

      setState(() {
        _selectedLocation = location;
        _loadingLocation = false;
        _locationDenied = false;
      });

      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;

        _mapController.move(
          location,
          16,
        );
      });
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _loadingLocation = false;
        _locationDenied = true;
        _selectedLocation = _defaultLocation;
      });
    }
  }

  void _selectLocation(LatLng location) {
    setState(() {
      _selectedLocation = location;
    });
  }

  Future<void> _openCamera() async {
    try {
      final cameras = await availableCameras();

      if (cameras.isEmpty) {
        if (!mounted) return;

        _showMessage(
          'Kamera tidak ditemukan.',
          isError: true,
        );

        return;
      }

      final camera = cameras.firstWhere(
        (item) => item.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );

      final result = await Navigator.of(context).push<XFile?>(
        MaterialPageRoute(
          builder: (_) => SigmaCameraPage(
            camera: camera,
          ),
        ),
      );

      if (result == null) return;

      final bytes = await result.readAsBytes();

      if (!mounted) return;

      setState(() {
        _photo = result;
        _photoBytes = bytes;
      });
    } on CameraException catch (error) {
      if (!mounted) return;

      String message;

      switch (error.code) {
        case 'CameraAccessDenied':
        case 'permissionDenied':
          message = 'Izin kamera ditolak.';
          break;
        case 'CameraAccessDeniedWithoutPrompt':
          message =
              'Akses kamera diblokir. Izinkan kamera melalui browser.';
          break;
        case 'CameraNotFound':
          message = 'Kamera tidak ditemukan.';
          break;
        default:
          message =
              error.description ?? 'Kamera tidak dapat dibuka.';
      }

      _showMessage(
        message,
        isError: true,
      );
    } catch (error) {
      if (!mounted) return;

      _showMessage(
        'Kamera tidak dapat dibuka: $error',
        isError: true,
      );
    }
  }

  Future<void> _pickFromGallery() async {
    try {
      final picked = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 85,
        maxWidth: 1920,
        maxHeight: 1920,
      );

      if (picked == null) return;

      final bytes = await picked.readAsBytes();

      if (!mounted) return;

      setState(() {
        _photo = picked;
        _photoBytes = bytes;
      });
    } catch (error) {
      if (!mounted) return;

      _showMessage(
        'Foto tidak dapat dipilih: $error',
        isError: true,
      );
    }
  }

  Future<void> _showPhotoOptions() async {
    await showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.only(
              bottom: 12,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const ListTile(
                  title: Text(
                    'Tambahkan foto',
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  subtitle: Text(
                    'Pilih cara untuk menambahkan foto laporan.',
                  ),
                ),
                ListTile(
                  leading: const CircleAvatar(
                    child: Icon(
                      Icons.camera_alt_outlined,
                    ),
                  ),
                  title: const Text('Ambil foto'),
                  subtitle: const Text(
                    'Gunakan kamera perangkat',
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    _openCamera();
                  },
                ),
                ListTile(
                  leading: const CircleAvatar(
                    child: Icon(
                      Icons.photo_library_outlined,
                    ),
                  ),
                  title: const Text('Pilih dari galeri'),
                  subtitle: const Text(
                    'Pilih foto yang sudah tersedia',
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    _pickFromGallery();
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;

    final location = _selectedLocation;

    if (location == null) {
      _showMessage(
        'Lokasi laporan belum tersedia.',
        isError: true,
      );

      return;
    }

    setState(() {
      _sending = true;
    });

    try {
      final report = await widget.api.submit(
        type: _type,
        latitude: location.latitude,
        longitude: location.longitude,
        description: _description.text.trim(),
        photo: _photo,
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            '${report.number} berhasil dikirim.',
          ),
        ),
      );

      Navigator.pop(context);
    } catch (error) {
      if (!mounted) return;

      _showMessage(
        error.toString().replaceFirst(
              'Exception: ',
              '',
            ),
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _sending = false;
        });
      }
    }
  }

  void _showMessage(
    String message, {
    bool isError = false,
  }) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor:
              isError ? Colors.red.shade700 : null,
        ),
      );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Form(
          key: _form,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(
              20,
              16,
              20,
              28,
            ),
            children: [
              const CitizenHeader(
                title: 'Kirim laporan',
                back: true,
              ),
              const SizedBox(height: 20),
              const Text(
                'Laporkan kondisi di sekitar Anda. '
                'Tambahkan foto agar sistem AI SIGMA dapat membantu '
                'menganalisis laporan.',
                style: TextStyle(
                  color: Colors.black54,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 20),
              _buildSectionTitle(
                icon: Icons.assignment_outlined,
                title: 'Informasi laporan',
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _type,
                decoration: const InputDecoration(
                  labelText: 'Jenis laporan',
                  prefixIcon: Icon(
                    Icons.category_outlined,
                  ),
                ),
                items: const [
                  DropdownMenuItem(
                    value: 'fire',
                    child: Text('Kebakaran'),
                  ),
                  DropdownMenuItem(
                    value: 'smoke',
                    child: Text('Asap'),
                  ),
                  DropdownMenuItem(
                    value: 'burning_activity',
                    child: Text(
                      'Aktivitas pembakaran',
                    ),
                  ),
                  DropdownMenuItem(
                    value: 'other',
                    child: Text('Lainnya'),
                  ),
                ],
                onChanged: _sending
                    ? null
                    : (value) {
                        if (value != null) {
                          setState(() {
                            _type = value;
                          });
                        }
                      },
              ),
              const SizedBox(height: 20),
              _buildSectionTitle(
                icon: Icons.camera_alt_outlined,
                title: 'Bukti foto',
              ),
              const SizedBox(height: 12),
              _buildPhotoSection(),
              const SizedBox(height: 20),
              _buildSectionTitle(
                icon: Icons.location_on_outlined,
                title: 'Lokasi kejadian',
              ),
              const SizedBox(height: 8),
              const Text(
                'Lokasi akan dibuat otomatis menggunakan lokasi perangkat. '
                'Ketuk peta untuk mengubah lokasi laporan.',
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.black54,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 12),
              _buildLocationSection(),
              const SizedBox(height: 20),
              _buildSectionTitle(
                icon: Icons.notes_outlined,
                title: 'Keterangan',
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _description,
                maxLength: 5000,
                maxLines: 5,
                decoration: const InputDecoration(
                  labelText: 'Keterangan laporan',
                  hintText:
                      'Jelaskan kondisi yang Anda lihat...',
                  alignLabelWithHint: true,
                ),
              ),
              const SizedBox(height: 12),
              FilledButton.icon(
                onPressed: _sending ? null : _submit,
                icon: _sending
                    ? const SizedBox.square(
                        dimension: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(
                        Icons.send_outlined,
                      ),
                label: Text(
                  _sending
                      ? 'Mengirim laporan...'
                      : 'Kirim laporan',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle({
    required IconData icon,
    required String title,
  }) {
    return Row(
      children: [
        Icon(
          icon,
          size: 20,
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }

  Widget _buildPhotoSection() {
    if (_photo == null || _photoBytes == null) {
      return InkWell(
        onTap: _sending ? null : _showPhotoOptions,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(
            horizontal: 20,
            vertical: 26,
          ),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: Theme.of(context).dividerColor,
            ),
          ),
          child: Column(
            children: [
              Icon(
                Icons.add_a_photo_outlined,
                size: 34,
                color: Theme.of(context).colorScheme.primary,
              ),
              const SizedBox(height: 10),
              const Text(
                'Tambahkan foto',
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Ambil foto atau pilih dari galeri',
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.black54,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(14),
          child: AspectRatio(
            aspectRatio: 16 / 9,
            child: Image.memory(
              _photoBytes!,
              fit: BoxFit.cover,
              width: double.infinity,
            ),
          ),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed:
                    _sending ? null : _showPhotoOptions,
                icon: const Icon(
                  Icons.edit_outlined,
                ),
                label: const Text('Ganti foto'),
              ),
            ),
            const SizedBox(width: 8),
            IconButton(
              onPressed: _sending
                  ? null
                  : () {
                      setState(() {
                        _photo = null;
                        _photoBytes = null;
                      });
                    },
              tooltip: 'Hapus foto',
              icon: const Icon(
                Icons.delete_outline,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildLocationSection() {
    final location = _selectedLocation;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: 300,
          width: double.infinity,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: Theme.of(context).dividerColor,
            ),
          ),
          child: Stack(
            children: [
              FlutterMap(
                mapController: _mapController,
                options: MapOptions(
                  initialCenter:
                      location ?? _defaultLocation,
                  initialZoom: location == null ? 13 : 16,
                  minZoom: 3,
                  maxZoom: 19,
                  onTap: (_, point) {
                    _selectLocation(point);
                  },
                ),
                children: [
                  TileLayer(
                    urlTemplate:
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName:
                        'com.sigma.apk_sigma',
                  ),
                  if (location != null)
                    MarkerLayer(
                      markers: [
                        Marker(
                          point: location,
                          width: 50,
                          height: 50,
                          child: const Icon(
                            Icons.location_on,
                            size: 46,
                            color: Colors.red,
                          ),
                        ),
                      ],
                    ),
                  RichAttributionWidget(
                    attributions: [
                      TextSourceAttribution(
                        'OpenStreetMap contributors',
                      ),
                    ],
                  ),
                ],
              ),
              if (_loadingLocation)
                Container(
                  color: Colors.white70,
                  child: const Center(
                    child: CircularProgressIndicator(),
                  ),
                ),
              Positioned(
                right: 12,
                top: 12,
                child: Material(
                  color: Colors.white,
                  elevation: 3,
                  borderRadius: BorderRadius.circular(10),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(10),
                    onTap: _initializeLocation,
                    child: const Padding(
                      padding: EdgeInsets.all(10),
                      child: Icon(
                        Icons.my_location,
                        color: Colors.red,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),
        if (_locationDenied)
          const Text(
            'Izin lokasi belum tersedia. Ketuk peta untuk menentukan lokasi secara manual.',
            style: TextStyle(
              fontSize: 12,
              color: Colors.orange,
            ),
          ),
        if (location != null) ...[
          const SizedBox(height: 8),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              'Latitude: ${location.latitude.toStringAsFixed(6)}\n'
              'Longitude: ${location.longitude.toStringAsFixed(6)}',
              style: const TextStyle(
                fontSize: 12,
                height: 1.5,
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class SigmaCameraPage extends StatefulWidget {
  const SigmaCameraPage({
    super.key,
    required this.camera,
  });

  final CameraDescription camera;

  @override
  State<SigmaCameraPage> createState() => _SigmaCameraPageState();
}

class _SigmaCameraPageState extends State<SigmaCameraPage> {
  late CameraController _controller;
  late Future<void> _initializeControllerFuture;

  bool _capturing = false;

  @override
  void initState() {
    super.initState();

    _controller = CameraController(
      widget.camera,
      ResolutionPreset.medium,
      enableAudio: false,
    );

    _initializeControllerFuture =
        _controller.initialize();
  }

  Future<void> _takePicture() async {
    if (_capturing) return;

    setState(() {
      _capturing = true;
    });

    try {
      await _initializeControllerFuture;

      final image = await _controller.takePicture();

      if (!mounted) return;

      Navigator.of(context).pop(image);
    } on CameraException catch (error) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            error.description ??
                'Gagal mengambil foto.',
          ),
          backgroundColor: Colors.red.shade700,
        ),
      );
    } catch (error) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Gagal mengambil foto: $error',
          ),
          backgroundColor: Colors.red.shade700,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _capturing = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text('Ambil foto'),
      ),
      body: FutureBuilder<void>(
        future: _initializeControllerFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState ==
                  ConnectionState.done &&
              _controller.value.isInitialized) {
            return Stack(
              fit: StackFit.expand,
              children: [
                CameraPreview(_controller),
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 30,
                  child: Center(
                    child: GestureDetector(
                      onTap:
                          _capturing ? null : _takePicture,
                      child: Container(
                        width: 76,
                        height: 76,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white,
                          border: Border.all(
                            color: Colors.white70,
                            width: 5,
                          ),
                        ),
                        child: _capturing
                            ? const Padding(
                                padding: EdgeInsets.all(22),
                                child:
                                    CircularProgressIndicator(
                                  strokeWidth: 3,
                                ),
                              )
                            : const Icon(
                                Icons.camera_alt,
                                size: 34,
                                color: Colors.black,
                              ),
                      ),
                    ),
                  ),
                ),
              ],
            );
          }

          if (snapshot.hasError) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment:
                      MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.no_photography_outlined,
                      size: 64,
                      color: Colors.white70,
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Kamera tidak dapat digunakan',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      '${snapshot.error}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          return const Center(
            child: CircularProgressIndicator(
              color: Colors.white,
            ),
          );
        },
      ),
    );
  }
}