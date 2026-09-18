import 'package:flutter_test/flutter_test.dart';

import 'package:apk_sigma/main.dart';

void main() {
  testWidgets('SIGMA role selection renders', (WidgetTester tester) async {
    await tester.pumpWidget(const SigmaApp());

    expect(find.text('SIGMA'), findsOneWidget);
    expect(find.text('Pilih akses anda'), findsOneWidget);
    expect(find.text('Masyarakat'), findsOneWidget);
    expect(find.text('Petugas'), findsOneWidget);
  });
}
