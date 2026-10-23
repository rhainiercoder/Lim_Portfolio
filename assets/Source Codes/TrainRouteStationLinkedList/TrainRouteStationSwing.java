import javax.swing.*;
import javax.swing.border.EmptyBorder;
import java.awt.*;
import java.awt.event.*;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class TrainRouteStationSwing extends JFrame {

    // color palette
    private static final Color OUTER_BLUE = new Color(0x2B18FF);
    private static final Color INNER_BLUE = new Color(0xAFC6FF);
    private static final Color CARD_BLUE = new Color(0xC8D6FF);
    private static final Color DARK_TEXT = new Color(0x050519);
    private static final Color BUTTON_BLUE = new Color(0x2B18FF);
    private static final Color BUTTON_BLUE_LIGHT = new Color(0x778CFF);
    private static final Color TRAIN_AVAILABLE = new Color(0x00C853); // green
    private static final Color TRAIN_UNAVAILABLE = new Color(0xFF3D3D); // red
    private static final Color WHITE = Color.WHITE;

    // --- SQLite DB config ---
    private static final String DB_URL = "jdbc:sqlite:train_routes.db";
    // table: stations(station_name, rail_line, availability)
    private static final String SQL_CREATE_TABLE =
            "CREATE TABLE IF NOT EXISTS stations (" +
                    "  id INTEGER PRIMARY KEY AUTOINCREMENT," +
                    "  station_name TEXT NOT NULL," +
                    "  rail_line TEXT NOT NULL," +
                    "  availability INTEGER NOT NULL," + // 1 = available, 0 = unavailable
                    "  UNIQUE(station_name, rail_line)" +
                    ");";
    private Connection db; // single connection

    private CardLayout rootLayout;
    private JPanel rootPanel;

    // manage screen components
    private JLabel lineLabel;
    private StationCarouselPanel stationCarousel;
    private JTextField moveField;

    // overlays (slide-up panels)
    private SlidingOverlayPanel editTrainsOverlay;
    private SlidingOverlayPanel railLinesOverlay;

    // badge label (upper-left in Edit Trains overlay)
    private JLabel availableCountLabel;

    // NEW: badge color will follow the most recently clicked station (any line)
    private Boolean lastClickedAvailability = null; // null = no click yet

    // data model: rail lines with circular station lists
    private static class RailLine {
        String name;
        List<String> stations = new ArrayList<>();
        List<Boolean> hasTrain = new ArrayList<>();

        RailLine(String name) {
            this.name = name;
        }

        void addStation(String stationName, boolean trainAvailable) {
            stations.add(stationName);
            hasTrain.add(trainAvailable);
        }

        int size() {
            return stations.size();
        }
    }

    private final List<RailLine> railLines = new ArrayList<>();
    private int currentLineIndex = 0;
    private int currentStationIndex = 0;

    // edit trains components
    private JPanel editTrainsLeftCol;
    private JPanel editTrainsRightCol;

    public TrainRouteStationSwing() {
        super("Manila Train Route Station Manager");
        initRailLines();
        initDatabase();
        syncModelWithDatabase();
        initFrame();
        initRoot();
    }

    // ---------- DB METHODS ----------

    private void initDatabase() {
        try {
            Class.forName("org.sqlite.JDBC");
            db = DriverManager.getConnection(DB_URL);
            try (Statement st = db.createStatement()) {
                st.execute(SQL_CREATE_TABLE);
            }
        } catch (Exception ex) {
            ex.printStackTrace();
            JOptionPane.showMessageDialog(this,
                    "Database error: " + ex.getMessage(),
                    "DB Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    /**
     * Seed DB if empty, then load availability from DB into model.
     */
    private void syncModelWithDatabase() {
        if (db == null) return;
        try (Statement st = db.createStatement()) {
            try (ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM stations")) {
                rs.next();
                if (rs.getInt(1) == 0) {
                    String insertSql =
                            "INSERT INTO stations(station_name, rail_line, availability) VALUES (?,?,?)";
                    try (PreparedStatement ps = db.prepareStatement(insertSql)) {
                        for (RailLine line : railLines) {
                            for (int i = 0; i < line.size(); i++) {
                                ps.setString(1, line.stations.get(i));
                                ps.setString(2, line.name);
                                ps.setInt(3, line.hasTrain.get(i) ? 1 : 0);
                                ps.addBatch();
                            }
                        }
                        ps.executeBatch();
                    }
                }
            }

            String selectSql = "SELECT station_name, rail_line, availability FROM stations";
            try (PreparedStatement ps = db.prepareStatement(selectSql);
                 ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    String stationName = rs.getString("station_name");
                    String railLine = rs.getString("rail_line");
                    boolean available = rs.getInt("availability") != 0;

                    RailLine line = findLineByName(railLine);
                    if (line == null) continue;
                    int idx = line.stations.indexOf(stationName);
                    if (idx >= 0) {
                        line.hasTrain.set(idx, available);
                    }
                }
            }
        } catch (Exception ex) {
            ex.printStackTrace();
        }
    }

    private RailLine findLineByName(String name) {
        for (RailLine rl : railLines) {
            if (rl.name.equals(name)) return rl;
        }
        return null;
    }

    private void saveStationAvailability(String stationName, String railLine, boolean available) {
        if (db == null) return;
        String sql = "UPDATE stations SET availability = ? WHERE station_name = ? AND rail_line = ?";
        try (PreparedStatement ps = db.prepareStatement(sql)) {
            ps.setInt(1, available ? 1 : 0);
            ps.setString(2, stationName);
            ps.setString(3, railLine);
            ps.executeUpdate();
        } catch (Exception ex) {
            ex.printStackTrace();
        }
    }

    private void saveLineAvailability(RailLine line) {
        if (db == null) return;
        String sql = "UPDATE stations SET availability = ? WHERE station_name = ? AND rail_line = ?";
        try (PreparedStatement ps = db.prepareStatement(sql)) {
            for (int i = 0; i < line.size(); i++) {
                ps.setInt(1, line.hasTrain.get(i) ? 1 : 0);
                ps.setString(2, line.stations.get(i));
                ps.setString(3, line.name);
                ps.addBatch();
            }
            ps.executeBatch();
        } catch (Exception ex) {
            ex.printStackTrace();
        }
    }

    // ---------- BADGE HELPERS ----------

    private RailLine currentLine() {
        return railLines.get(currentLineIndex);
    }

    private int getAvailableTrainCount(RailLine line) {
        int count = 0;
        for (Boolean b : line.hasTrain) {
            if (Boolean.TRUE.equals(b)) count++;
        }
        return count;
    }

    /**
     * Badge:
     * - Text = available count for current line
     * - Color = last clicked station availability (ANY station)
     */
    private void refreshBadge() {
        if (availableCountLabel == null) return;

        availableCountLabel.setText(String.valueOf(getAvailableTrainCount(currentLine())));

        if (lastClickedAvailability == null) {
            // default if nothing clicked yet
            availableCountLabel.setBackground(TRAIN_UNAVAILABLE);
        } else {
            availableCountLabel.setBackground(lastClickedAvailability ? TRAIN_AVAILABLE : TRAIN_UNAVAILABLE);
        }
        availableCountLabel.repaint();
    }

    private JLabel createCountBadgeLabel() {
        JLabel badge = new JLabel("0", SwingConstants.CENTER);
        badge.setOpaque(true);
        badge.setBackground(TRAIN_UNAVAILABLE);
        badge.setForeground(WHITE);
        badge.setFont(new Font("Impact", Font.BOLD, 16));
        badge.setBorder(BorderFactory.createEmptyBorder(2, 8, 2, 8));
        badge.setPreferredSize(new Dimension(34, 26));
        return badge;
    }

    // ---------- UI + APP ----------

    private void initRailLines() {
        RailLine lrt1 = new RailLine("LRT 1");
        lrt1.addStation("Fernando Poe Jr.", true);
        lrt1.addStation("Balintawak", false);
        lrt1.addStation("Monumento", true);
        lrt1.addStation("5th Avenue", false);
        lrt1.addStation("R. Papa", true);
        lrt1.addStation("Abad Santos", false);
        lrt1.addStation("Blumentritt", true);
        lrt1.addStation("Tayuman", false);
        lrt1.addStation("Bambang", true);
        lrt1.addStation("Doroteo Jose", false);
        lrt1.addStation("Carriedo", true);
        lrt1.addStation("Central Terminal", false);
        lrt1.addStation("United Nations", true);
        lrt1.addStation("Pedro Gil", false);
        lrt1.addStation("Quirino", true);
        lrt1.addStation("Vito Cruz", false);
        lrt1.addStation("Gil Puyat", true);
        lrt1.addStation("Libertad", false);
        lrt1.addStation("EDSA", true);
        lrt1.addStation("Baclaran", false);
        lrt1.addStation("Redemptorist-Aseana", true);
        lrt1.addStation("MIA Road", false);
        lrt1.addStation("PITX", true);
        lrt1.addStation("Ninoy Aquino Avenue", false);
        lrt1.addStation("Dr. Santos", true);
        railLines.add(lrt1);

        RailLine lrt2 = new RailLine("LRT 2");
        lrt2.addStation("Recto", true);
        lrt2.addStation("Legarda", false);
        lrt2.addStation("Pureza", true);
        lrt2.addStation("V. Mapa", false);
        lrt2.addStation("J. Ruiz", true);
        lrt2.addStation("Gilmore", false);
        lrt2.addStation("Betty Go-Belmonte", true);
        lrt2.addStation("Araneta Center-Cubao", false);
        lrt2.addStation("Anonas", true);
        lrt2.addStation("Katipunan", false);
        lrt2.addStation("Santolan", true);
        lrt2.addStation("Marikina-Pasig", false);
        lrt2.addStation("Antipolo", true);
        railLines.add(lrt2);

        RailLine mrt3 = new RailLine("MRT 3");
        mrt3.addStation("North Avenue", true);
        mrt3.addStation("Quezon Avenue", false);
        mrt3.addStation("GMA-Kamuning", true);
        mrt3.addStation("Araneta Center-Cubao", false);
        mrt3.addStation("Santolan-Annapolis", true);
        mrt3.addStation("Ortigas", false);
        mrt3.addStation("Shaw Boulevard", true);
        mrt3.addStation("Boni", false);
        mrt3.addStation("Guadalupe", true);
        mrt3.addStation("Buendia", false);
        mrt3.addStation("Ayala", true);
        mrt3.addStation("Magallanes", false);
        mrt3.addStation("Taft Avenue", true);
        railLines.add(mrt3);

        RailLine pnr = new RailLine("PNR");
        pnr.addStation("Tutuban", true);
        pnr.addStation("Blumentritt", false);
        pnr.addStation("Laon Laan", true);
        pnr.addStation("España", false);
        pnr.addStation("Sta. Mesa", true);
        pnr.addStation("Pandacan", false);
        pnr.addStation("Paco", true);
        pnr.addStation("San Andres", false);
        pnr.addStation("Vito Cruz", true);
        pnr.addStation("Buendia", false);
        pnr.addStation("Dela Rosa", true);
        pnr.addStation("Pasay Road", false);
        pnr.addStation("EDSA", true);
        pnr.addStation("Nichols", false);
        pnr.addStation("FTI (formerly Arca South)", true);
        pnr.addStation("Bicutan", false);
        pnr.addStation("Sucat", true);
        pnr.addStation("Alabang", false);
        pnr.addStation("Muntinlupa", true);
        pnr.addStation("San Pedro", false);
        pnr.addStation("Pacita Main Gate", true);
        pnr.addStation("Golden City 1", false);
        pnr.addStation("Biñan", true);
        pnr.addStation("Sta. Rosa", false);
        pnr.addStation("Cabuyao", true);
        pnr.addStation("Mamatid", false);
        pnr.addStation("Calamba", true);
        pnr.addStation("IRRI (UP Los Baños)", false);
        railLines.add(pnr);
    }

    private void initFrame() {
        setDefaultCloseOperation(WindowConstants.EXIT_ON_CLOSE);
        setMinimumSize(new Dimension(900, 600));
        getContentPane().setBackground(OUTER_BLUE);
        setLayout(new BorderLayout());
    }

    private void initRoot() {
        rootLayout = new CardLayout();
        rootPanel = new JPanel(rootLayout);
        rootPanel.setOpaque(false);

        JPanel welcome = buildWelcomeScreen();
        JPanel manage = buildManageScreen();

        rootPanel.add(welcome, "WELCOME");
        rootPanel.add(manage, "MANAGE");

        add(rootPanel, BorderLayout.CENTER);

        pack();
        setLocationRelativeTo(null);
    }

    private JPanel buildWelcomeScreen() {
        JPanel wrapper = new JPanel(new GridBagLayout());
        wrapper.setBackground(OUTER_BLUE);

        RoundedPanel inner = new RoundedPanel(40, INNER_BLUE);
        inner.setLayout(new BorderLayout());
        inner.setBorder(new EmptyBorder(40, 40, 40, 40));

        JLabel title = new JLabel(
                "<html><div style='text-align:center;'>WELCOME TO TRAIN<br/>ROUTE STATION<br/>MANAGER</div></html>"
        );
        title.setHorizontalAlignment(SwingConstants.CENTER);
        title.setForeground(DARK_TEXT);
        title.setFont(new Font("Impact", Font.BOLD, 40));
        inner.add(title, BorderLayout.CENTER);

        JPanel bottom = new JPanel();
        bottom.setOpaque(false);
        JButton startBtn = new JButton("START THE ENGINE");
        startBtn.setFocusPainted(false);
        startBtn.setBackground(BUTTON_BLUE);
        startBtn.setForeground(WHITE);
        startBtn.setFont(new Font("Impact", Font.PLAIN, 18));
        startBtn.setBorder(BorderFactory.createEmptyBorder(10, 30, 10, 30));
        startBtn.addActionListener(e -> rootLayout.show(rootPanel, "MANAGE"));
        bottom.add(startBtn);
        inner.add(bottom, BorderLayout.SOUTH);

        wrapper.add(inner);
        return wrapper;
    }

    private JPanel buildManageScreen() {
        JPanel wrapper = new JPanel(new GridBagLayout());
        wrapper.setBackground(OUTER_BLUE);

        RoundedPanel inner = new RoundedPanel(40, INNER_BLUE);
        inner.setLayout(new BorderLayout());
        inner.setBorder(new EmptyBorder(5, 30, 20, 30));
        wrapper.add(inner);

        inner.add(buildManageHeader(), BorderLayout.NORTH);

        JLayeredPane layered = new JLayeredPane() {
            @Override public Dimension getPreferredSize() { return new Dimension(820, 360); }
            @Override public boolean isOptimizedDrawingEnabled() { return false; }
        };
        layered.setOpaque(false);

        JPanel baseContent = buildManageBaseContent();
        baseContent.setBounds(0, 0, 820, 360);
        layered.add(baseContent, JLayeredPane.DEFAULT_LAYER);

        editTrainsOverlay = buildEditTrainsOverlay();
        editTrainsOverlay.setBounds(0, 0, 820, 360);
        layered.add(editTrainsOverlay, JLayeredPane.PALETTE_LAYER);
        editTrainsOverlay.setVisible(false);

        railLinesOverlay = buildRailLinesOverlay();
        railLinesOverlay.setBounds(0, 0, 820, 360);
        layered.add(railLinesOverlay, JLayeredPane.PALETTE_LAYER);
        railLinesOverlay.setVisible(false);

        inner.add(layered, BorderLayout.CENTER);

        return wrapper;
    }

    private JPanel buildManageHeader() {
        JPanel header = new JPanel();
        header.setOpaque(false);
        header.setLayout(new BorderLayout());

        JPanel left = new JPanel(new FlowLayout(FlowLayout.LEFT, 0, 0));
        left.setOpaque(false);
        RoundedButton back = new RoundedButton("<", 10);
        styleIconButton(back);
        back.setPreferredSize(new Dimension(40, 40));
        back.addActionListener(e -> rootLayout.show(rootPanel, "WELCOME"));
        left.add(back);
        left.setPreferredSize(new Dimension(180, 40));
        header.add(left, BorderLayout.WEST);

        JLabel title = new JLabel("MANAGE STATIONS", SwingConstants.CENTER);
        title.setForeground(OUTER_BLUE);
        title.setFont(new Font("Impact", Font.BOLD, 40));
        header.add(title, BorderLayout.CENTER);

        JPanel legend = new JPanel();
        legend.setOpaque(false);
        legend.setLayout(new BoxLayout(legend, BoxLayout.Y_AXIS));
        legend.add(buildLegendRow(TRAIN_AVAILABLE, "TRAIN AVAILABLE"));
        legend.add(Box.createVerticalStrut(6));
        legend.add(buildLegendRow(TRAIN_UNAVAILABLE, "TRAIN UNAVAILABLE"));
        legend.setPreferredSize(new Dimension(180, 40));
        header.add(legend, BorderLayout.EAST);

        return header;
    }

    private JPanel buildLegendRow(Color color, String text) {
        JPanel row = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 0));
        row.setOpaque(false);
        JLabel square = new JLabel();
        square.setOpaque(true);
        square.setBackground(color);
        square.setPreferredSize(new Dimension(18, 18));
        JLabel label = new JLabel(text);
        label.setFont(new Font("Arial", Font.BOLD, 11));
        label.setForeground(OUTER_BLUE);
        row.add(square);
        row.add(label);
        return row;
    }

    private JPanel buildManageBaseContent() {
        JPanel base = new JPanel();
        base.setOpaque(false);
        base.setLayout(new BorderLayout());

        JPanel topLine = new JPanel(new FlowLayout(FlowLayout.CENTER, 0, 10));
        topLine.setOpaque(false);
        RoundedPanel linePanel = new RoundedPanel(20, BUTTON_BLUE);
        linePanel.setLayout(new BorderLayout());
        linePanel.setPreferredSize(new Dimension(160, 40));
        lineLabel = new JLabel(currentLine().name, SwingConstants.CENTER);
        lineLabel.setForeground(WHITE);
        lineLabel.setFont(new Font("Impact", Font.PLAIN, 18));
        linePanel.add(lineLabel, BorderLayout.CENTER);
        topLine.add(linePanel);
        base.add(topLine, BorderLayout.NORTH);

        JPanel center = new JPanel(new BorderLayout());
        center.setOpaque(false);

        CircleButton leftArrow = new CircleButton("<", 40);
        styleIconButton(leftArrow);
        leftArrow.addActionListener(e -> stationCarousel.previousStation());
        center.add(leftArrow, BorderLayout.WEST);

        CircleButton rightArrow = new CircleButton(">", 40);
        styleIconButton(rightArrow);
        rightArrow.addActionListener(e -> stationCarousel.nextStation());
        center.add(rightArrow, BorderLayout.EAST);

        stationCarousel = new StationCarouselPanel();
        center.add(stationCarousel, BorderLayout.CENTER);
        base.add(center, BorderLayout.CENTER);

        JPanel bottom = new JPanel(new GridBagLayout());
        bottom.setOpaque(false);
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.gridy = 0;
        gbc.insets = new Insets(10, 20, 0, 20);

        RoundedButton editTrain = new RoundedButton("EDIT TRAIN", 20);
        styleMainButton(editTrain);
        editTrain.setPreferredSize(new Dimension(160, 40));
        editTrain.addActionListener(e -> {
            rebuildEditTrainsOverlay();
            editTrainsOverlay.slideInFromBottom();
        });

        RoundedButton changeLine = new RoundedButton("CHANGE RAIL LINE", 20);
        styleMainButton(changeLine);
        changeLine.setPreferredSize(new Dimension(210, 40));
        changeLine.addActionListener(e -> {
            rebuildRailLinesOverlay();
            railLinesOverlay.slideInFromBottom();
        });

        JLabel moveLabel = new JLabel("MOVE TRAIN");
        moveLabel.setFont(new Font("Arial", Font.BOLD, 14));
        moveLabel.setForeground(OUTER_BLUE);

        moveField = new JTextField(4);
        moveField.setHorizontalAlignment(SwingConstants.CENTER);
        moveField.setFont(new Font("Arial", Font.BOLD, 14));
        moveField.setForeground(OUTER_BLUE);
        moveField.setBorder(BorderFactory.createLineBorder(OUTER_BLUE, 2));
        moveField.setBackground(WHITE);
        moveField.setPreferredSize(new Dimension(70, 32));
        moveField.addActionListener(e -> handleMoveField());

        gbc.gridx = 0;
        bottom.add(editTrain, gbc);
        gbc.gridx = 1;
        bottom.add(changeLine, gbc);
        gbc.gridx = 2;
        bottom.add(moveLabel, gbc);
        gbc.gridx = 3;
        bottom.add(moveField, gbc);

        base.add(bottom, BorderLayout.SOUTH);
        return base;
    }

    private void handleMoveField() {
        String txt = moveField.getText().trim();
        if (txt.isEmpty()) return;
        try {
            int steps = Integer.parseInt(txt);
            if (steps <= 0) {
                flashFieldError();
                return;
            }
            moveTrains(steps);
            stationCarousel.repaint();
            rebuildEditTrainsOverlay();
        } catch (NumberFormatException ex) {
            flashFieldError();
        }
    }

    private void moveTrains(int steps) {
        RailLine line = currentLine();
        int n = line.size();
        if (n == 0) return;
        steps = steps % n;
        if (steps == 0) return;

        List<Boolean> old = new ArrayList<>(line.hasTrain);
        for (int i = 0; i < n; i++) {
            int src = (i - steps + n) % n;
            line.hasTrain.set(i, old.get(src));
        }
        saveLineAvailability(line);
        refreshBadge();
    }

    private void flashFieldError() {
        Color old = moveField.getBackground();
        moveField.setBackground(new Color(0xFFCCCC));
        Timer t = new Timer(150, e -> moveField.setBackground(old));
        t.setRepeats(false);
        t.start();
    }

    // edit trains overlay

    private SlidingOverlayPanel buildEditTrainsOverlay() {
        SlidingOverlayPanel overlay = new SlidingOverlayPanel();
        overlay.setOpaque(false);

        RoundedPanel card = new RoundedPanel(40, WHITE);
        card.setLayout(new BorderLayout());
        card.setBorder(new EmptyBorder(16, 40, 20, 40));

        JPanel topBar = new JPanel(new BorderLayout());
        topBar.setOpaque(false);

        JPanel left = new JPanel(new FlowLayout(FlowLayout.LEFT, 0, 0));
        left.setOpaque(false);
        availableCountLabel = createCountBadgeLabel();
        left.add(availableCountLabel);
        topBar.add(left, BorderLayout.WEST);

        JLabel title = new JLabel("EDIT TRAINS", SwingConstants.CENTER);
        title.setForeground(OUTER_BLUE);
        title.setFont(new Font("Impact", Font.PLAIN, 22));
        topBar.add(title, BorderLayout.CENTER);

        RoundedButton down = new RoundedButton("v", 20);
        down.setForeground(WHITE);
        down.setBackground(BUTTON_BLUE);
        down.setFont(new Font("Impact", Font.PLAIN, 14));
        down.setPreferredSize(new Dimension(40, 32));
        down.addActionListener(e -> overlay.slideOutToBottom());
        JPanel right = new JPanel(new FlowLayout(FlowLayout.RIGHT, 0, 0));
        right.setOpaque(false);
        right.add(down);
        topBar.add(right, BorderLayout.EAST);

        card.add(topBar, BorderLayout.NORTH);

        JPanel cols = new JPanel(new GridLayout(1, 2, 30, 0));
        cols.setOpaque(false);

        editTrainsLeftCol = new JPanel();
        editTrainsLeftCol.setOpaque(false);
        editTrainsLeftCol.setLayout(new BoxLayout(editTrainsLeftCol, BoxLayout.Y_AXIS));

        editTrainsRightCol = new JPanel();
        editTrainsRightCol.setOpaque(false);
        editTrainsRightCol.setLayout(new BoxLayout(editTrainsRightCol, BoxLayout.Y_AXIS));

        cols.add(editTrainsLeftCol);
        cols.add(editTrainsRightCol);

        JScrollPane scroll = new JScrollPane(cols);
        scroll.setBorder(null);
        scroll.setOpaque(false);
        scroll.getViewport().setOpaque(false);
        scroll.setHorizontalScrollBarPolicy(ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);
        scroll.setVerticalScrollBarPolicy(ScrollPaneConstants.VERTICAL_SCROLLBAR_AS_NEEDED);

        card.add(scroll, BorderLayout.CENTER);

        overlay.setCard(card);
        rebuildEditTrainsOverlay();
        return overlay;
    }

    private void rebuildEditTrainsOverlay() {
        if (editTrainsLeftCol == null || editTrainsRightCol == null) return;

        refreshBadge();

        editTrainsLeftCol.removeAll();
        editTrainsRightCol.removeAll();

        RailLine line = currentLine();
        int n = line.size();
        int half = (n + 1) / 2;

        for (int i = 0; i < n; i++) {
            int index = i;

            JPanel row = new JPanel(new FlowLayout(FlowLayout.LEFT, 8, 2));
            row.setOpaque(false);

            JLabel dot = new JLabel();
            dot.setOpaque(true);
            dot.setPreferredSize(new Dimension(12, 12));
            dot.setBackground(line.hasTrain.get(i) ? TRAIN_AVAILABLE : TRAIN_UNAVAILABLE);
            dot.setCursor(Cursor.getPredefinedCursor(Cursor.HAND_CURSOR));

            dot.addMouseListener(new MouseAdapter() {
                @Override
                public void mouseClicked(MouseEvent e) {
                    boolean current = line.hasTrain.get(index);
                    boolean newVal = !current;
                    line.hasTrain.set(index, newVal);

                    // update dot
                    dot.setBackground(newVal ? TRAIN_AVAILABLE : TRAIN_UNAVAILABLE);

                    // NEW: badge color follows this clicked station
                    lastClickedAvailability = newVal;

                    // refresh badge + cards
                    refreshBadge();
                    stationCarousel.repaint();

                    // persist
                    saveStationAvailability(line.stations.get(index), line.name, newVal);
                }
            });

            JLabel label = new JLabel(line.stations.get(i));
            label.setForeground(OUTER_BLUE);
            label.setFont(new Font("Arial", Font.BOLD, 13));

            row.add(dot);
            row.add(label);

            if (i < half) editTrainsLeftCol.add(row);
            else editTrainsRightCol.add(row);
        }

        editTrainsLeftCol.revalidate();
        editTrainsRightCol.revalidate();
        editTrainsLeftCol.repaint();
        editTrainsRightCol.repaint();
    }

    // rail lines overlay

    private SlidingOverlayPanel buildRailLinesOverlay() {
        SlidingOverlayPanel overlay = new SlidingOverlayPanel();
        overlay.setOpaque(false);

        RoundedPanel card = new RoundedPanel(40, WHITE);
        overlay.setCard(card);
        rebuildRailLinesOverlay();
        return overlay;
    }

    private void rebuildRailLinesOverlay() {
        if (railLinesOverlay == null) return;
        RoundedPanel card = railLinesOverlay.card;
        if (card == null) return;

        card.removeAll();
        card.setLayout(new BorderLayout());
        card.setBorder(new EmptyBorder(16, 60, 20, 60));

        JPanel topBar = new JPanel(new BorderLayout());
        topBar.setOpaque(false);
        JLabel title = new JLabel("RAIL LINES", SwingConstants.CENTER);
        title.setForeground(OUTER_BLUE);
        title.setFont(new Font("Impact", Font.PLAIN, 22));
        topBar.add(title, BorderLayout.CENTER);

        RoundedButton down = new RoundedButton("v", 20);
        down.setForeground(WHITE);
        down.setBackground(BUTTON_BLUE);
        down.setFont(new Font("Impact", Font.PLAIN, 14));
        down.setPreferredSize(new Dimension(40, 32));
        down.addActionListener(e -> railLinesOverlay.slideOutToBottom());
        JPanel right = new JPanel(new FlowLayout(FlowLayout.RIGHT, 0, 0));
        right.setOpaque(false);
        right.add(down);
        topBar.add(right, BorderLayout.EAST);

        card.add(topBar, BorderLayout.NORTH);

        JPanel linesPanel = new JPanel();
        linesPanel.setOpaque(false);
        linesPanel.setLayout(new BoxLayout(linesPanel, BoxLayout.Y_AXIS));
        linesPanel.add(Box.createVerticalStrut(10));

        for (int i = 0; i < railLines.size(); i++) {
            int idx = i;
            RoundedButton lineBtn = new RoundedButton(railLines.get(i).name, 20);
            lineBtn.setAlignmentX(Component.CENTER_ALIGNMENT);
            lineBtn.setMaximumSize(new Dimension(200, 40));
            lineBtn.setPreferredSize(new Dimension(200, 40));
            lineBtn.setForeground(WHITE);
            lineBtn.setFont(new Font("Impact", Font.PLAIN, 16));
            lineBtn.setBackground(i == currentLineIndex ? BUTTON_BLUE : BUTTON_BLUE_LIGHT);

            lineBtn.addActionListener(e -> {
                currentLineIndex = idx;
                lineLabel.setText(currentLine().name);
                currentStationIndex = 0;
                railLinesOverlay.slideOutToBottom();
                stationCarousel.repaint();
                rebuildEditTrainsOverlay(); // refresh list + badge for new line
            });

            linesPanel.add(lineBtn);
            linesPanel.add(Box.createVerticalStrut(8));
        }

        card.add(linesPanel, BorderLayout.CENTER);
        card.revalidate();
        card.repaint();
    }

    // helper button styles
    private void styleMainButton(RoundedButton b) {
        b.setBackground(BUTTON_BLUE);
        b.setForeground(WHITE);
        b.setFont(new Font("Impact", Font.PLAIN, 16));
    }

    private void styleIconButton(AbstractButton b) {
        b.setBackground(BUTTON_BLUE);
        b.setForeground(WHITE);
        b.setFont(new Font("Impact", Font.BOLD, 18));
    }

    // sliding overlay implementation
    private static class SlidingOverlayPanel extends JPanel {
        RoundedPanel card;
        private Timer slideTimer;

        SlidingOverlayPanel() {
            setLayout(null);
            setVisible(false);

            addMouseListener(new MouseAdapter() {
                @Override
                public void mouseClicked(MouseEvent e) {
                    if (card != null && !card.getBounds().contains(e.getPoint())) {
                        slideOutToBottom();
                    }
                }
            });
        }

        void setCard(RoundedPanel card) {
            this.card = card;
            add(card);
        }

        @Override
        public void doLayout() {
            if (card != null) {
                int w = getWidth();
                int h = getHeight();
                if (w == 0 || h == 0) return;

                int cw = Math.min(500, (int) (w * 0.7));
                int ch = Math.min(350, (int) (h * 0.8));
                int x = (w - cw) / 2;

                int y = card.getY();
                if (y == 0 || y > h) y = h;
                card.setBounds(x, y, cw, ch);
            }
        }

        void slideInFromBottom() {
            if (card == null) return;
            if (slideTimer != null && slideTimer.isRunning()) slideTimer.stop();

            setVisible(true);

            int w = getWidth();
            int h = getHeight();
            if (w == 0 || h == 0) {
                Container p = getParent();
                if (p != null) {
                    w = p.getWidth();
                    h = p.getHeight();
                }
            }
            int cw = Math.min(500, (int) (w * 0.7));
            int ch = Math.min(350, (int) (h * 0.8));
            int x = (w - cw) / 2;
            int startY = h;
            int endY = (h - ch) / 2;

            card.setBounds(x, startY, cw, ch);

            int distance = startY - endY;
            int steps = 20;
            double step = distance / (double) steps;

            slideTimer = new Timer(15, null);
            slideTimer.addActionListener(new ActionListener() {
                int count = 0;
                double currentY = startY;

                @Override
                public void actionPerformed(ActionEvent e) {
                    currentY -= step;
                    count++;
                    if (count >= steps) {
                        currentY = endY;
                        slideTimer.stop();
                    }
                    card.setLocation(x, (int) currentY);
                    repaint();
                }
            });
            slideTimer.start();
        }

        void slideOutToBottom() {
            if (card == null) {
                setVisible(false);
                return;
            }
            if (slideTimer != null && slideTimer.isRunning()) slideTimer.stop();

            int h = getHeight();
            Rectangle bounds = card.getBounds();
            int x = bounds.x;
            int startY = bounds.y;
            int endY = h;

            int distance = endY - startY;
            int steps = 20;
            double step = distance / (double) steps;

            slideTimer = new Timer(15, null);
            slideTimer.addActionListener(new ActionListener() {
                int count = 0;
                double currentY = startY;

                @Override
                public void actionPerformed(ActionEvent e) {
                    currentY += step;
                    count++;
                    if (count >= steps) {
                        currentY = endY;
                        slideTimer.stop();
                        setVisible(false);
                    }
                    card.setLocation(x, (int) currentY);
                    repaint();
                }
            });
            slideTimer.start();
        }

        @Override
        protected void paintComponent(Graphics g) {
            if (!isVisible()) return;
            Graphics2D g2 = (Graphics2D) g.create();
            g2.setColor(new Color(0, 0, 0, 80));
            g2.fillRect(0, 0, getWidth(), getHeight());
            g2.dispose();
        }
    }

    // station carousel
    private class StationCarouselPanel extends JPanel {
        StationCarouselPanel() {
            setOpaque(false);
        }

        void previousStation() {
            RailLine line = currentLine();
            if (line.size() == 0) return;
            currentStationIndex = (currentStationIndex - 1 + line.size()) % line.size();
            repaint();
        }

        void nextStation() {
            RailLine line = currentLine();
            if (line.size() == 0) return;
            currentStationIndex = (currentStationIndex + 1) % line.size();
            repaint();
        }

        @Override
        protected void paintComponent(Graphics g) {
            super.paintComponent(g);
            Graphics2D g2 = (Graphics2D) g.create();
            g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);

            RailLine line = currentLine();
            int n = line.size();
            if (n == 0) {
                g2.dispose();
                return;
            }

            int w = getWidth();

            int cardWidth = 180;
            int cardHeight = 210;
            int gap = 40;
            int cardY = 40;

            int totalWidth = cardWidth * 3 + gap * 2;
            int startX = (w - totalWidth) / 2;

            int idxCenter = currentStationIndex;
            int idxLeft = (idxCenter - 1 + n) % n;
            int idxRight = (idxCenter + 1) % n;

            int leftX = startX;
            int centerCardX = startX + cardWidth + gap;
            int rightX = startX + 2 * (cardWidth + gap);

            paintStationCard(g2, line, idxLeft, leftX, cardY, cardWidth, cardHeight, false);
            paintStationCard(g2, line, idxCenter, centerCardX, cardY, cardWidth, cardHeight, true);
            paintStationCard(g2, line, idxRight, rightX, cardY, cardWidth, cardHeight, false);

            g2.dispose();
        }

        private void paintStationCard(Graphics2D g2, RailLine line, int idx,
                                      int x, int y, int w, int h, boolean center) {
            RoundedPanel.paintRoundedRect(g2, CARD_BLUE, x, y, w, h, 40);

            boolean hasTrain = line.hasTrain.get(idx);
            Color iconColor = hasTrain ? TRAIN_AVAILABLE : TRAIN_UNAVAILABLE;

            int iconSize = 80;
            int iconX = x + (w - iconSize) / 2;
            int iconY = y + 25;

            g2.setColor(iconColor);
            g2.fillRoundRect(iconX, iconY, iconSize, iconSize, 20, 20);

            g2.setColor(WHITE);
            g2.fillRoundRect(iconX + 18, iconY + 18, iconSize - 36, 26, 10, 10);
            g2.fillOval(iconX + 18, iconY + iconSize - 18, 12, 12);
            g2.fillOval(iconX + iconSize - 30, iconY + iconSize - 18, 12, 12);

            g2.setColor(OUTER_BLUE);
            g2.setFont(new Font("Impact", Font.PLAIN, center ? 16 : 13));
            String text = line.stations.get(idx).toUpperCase();

            int textAreaWidth = w - 20;
            int textBaseline = y + h - 40;

            drawFittedCenteredText(g2, text, x, textBaseline, textAreaWidth);
        }

        private void drawFittedCenteredText(Graphics2D g2, String text,
                                            int cardX, int baselineY, int maxWidth) {
            Font originalFont = g2.getFont();

            int minSize = 8;
            for (int size = originalFont.getSize(); size >= minSize; size--) {
                Font font = originalFont.deriveFont((float) size);
                g2.setFont(font);
                FontMetrics fm = g2.getFontMetrics();
                if (fm.stringWidth(text) <= maxWidth) {
                    int textWidth = fm.stringWidth(text);
                    int tx = cardX + (maxWidth - textWidth) / 2 + 10;
                    g2.drawString(text, tx, baselineY);
                    g2.setFont(originalFont);
                    return;
                }
            }

            g2.setFont(originalFont.deriveFont(10f));
            FontMetrics fm = g2.getFontMetrics();

            String firstLine = text;
            String secondLine = "";

            int breakPos = text.lastIndexOf(' ');
            while (breakPos > 0) {
                String line1 = text.substring(0, breakPos);
                String line2 = text.substring(breakPos + 1);
                if (fm.stringWidth(line1) <= maxWidth && fm.stringWidth(line2) <= maxWidth) {
                    firstLine = line1;
                    secondLine = line2;
                    break;
                }
                breakPos = text.lastIndexOf(' ', breakPos - 1);
            }

            int lineHeight = fm.getHeight();
            int totalHeight = secondLine.isEmpty() ? lineHeight : lineHeight * 2;
            int firstBaseline = baselineY - (totalHeight - lineHeight);
            int secondBaseline = firstBaseline + lineHeight;

            int w1 = fm.stringWidth(firstLine);
            int tx1 = cardX + (maxWidth - w1) / 2 + 10;
            g2.drawString(firstLine, tx1, firstBaseline);

            if (!secondLine.isEmpty()) {
                int w2 = fm.stringWidth(secondLine);
                int tx2 = cardX + (maxWidth - w2) / 2 + 10;
                g2.drawString(secondLine, tx2, secondBaseline);
            }

            g2.setFont(originalFont);
        }
    }

    // rounded helpers
    private static class RoundedPanel extends JPanel {
        private final int radius;
        private final Color bg;

        RoundedPanel(int radius, Color bg) {
            this.radius = radius;
            this.bg = bg;
            setOpaque(false);
        }

        static void paintRoundedRect(Graphics2D g2, Color color,
                                     int x, int y, int w, int h, int radius) {
            g2.setColor(color);
            g2.fillRoundRect(x, y, w, h, radius, radius);
        }

        @Override
        protected void paintComponent(Graphics g) {
            Graphics2D g2 = (Graphics2D) g.create();
            g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
            paintRoundedRect(g2, bg, 0, 0, getWidth(), getHeight(), radius);
            g2.dispose();
            super.paintComponent(g);
        }
    }

    private static class RoundedButton extends JButton {
        private final int radius;

        RoundedButton(String text, int radius) {
            super(text);
            this.radius = radius;
            setContentAreaFilled(false);
            setFocusPainted(false);
            setBorderPainted(false);
        }

        @Override
        protected void paintComponent(Graphics g) {
            Graphics2D g2 = (Graphics2D) g.create();
            g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
            g2.setColor(getBackground());
            g2.fillRoundRect(0, 0, getWidth(), getHeight(), radius, radius);

            FontMetrics fm = g2.getFontMetrics(getFont());
            int textWidth = fm.stringWidth(getText());
            int textHeight = fm.getAscent();
            int x = (getWidth() - textWidth) / 2;
            int y = (getHeight() + textHeight) / 2 - 2;

            g2.setColor(getForeground());
            g2.setFont(getFont());
            g2.drawString(getText(), x, y);
            g2.dispose();
        }

        @Override public void setContentAreaFilled(boolean b) {}
        @Override public void setBorderPainted(boolean b) {}
    }

    private static class CircleButton extends JButton {
        private final int diameter;

        CircleButton(String text, int diameter) {
            super(text);
            this.diameter = diameter;

            setOpaque(false);
            setContentAreaFilled(false);
            setFocusPainted(false);
            setBorderPainted(false);
            setBorder(null);

            Dimension d = new Dimension(diameter, diameter);
            setPreferredSize(d);
            setMinimumSize(d);
            setMaximumSize(d);
        }

        @Override public Dimension getPreferredSize() { return new Dimension(diameter, diameter); }
        @Override public Dimension getMinimumSize() { return getPreferredSize(); }
        @Override public Dimension getMaximumSize() { return getPreferredSize(); }

        @Override
        protected void paintComponent(Graphics g) {
            int size = Math.min(getWidth(), getHeight());
            int xOffset = (getWidth() - size) / 2;
            int yOffset = (getHeight() - size) / 2;

            Graphics2D g2 = (Graphics2D) g.create();
            g2.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);

            g2.setColor(getBackground());
            g2.fillOval(xOffset, yOffset, size, size);

            g2.setFont(getFont());
            FontMetrics fm = g2.getFontMetrics();
            int textWidth = fm.stringWidth(getText());
            int textHeight = fm.getAscent();
            int tx = getWidth() / 2 - textWidth / 2;
            int ty = getHeight() / 2 + textHeight / 2 - 2;

            g2.setColor(getForeground());
            g2.drawString(getText(), tx, ty);
            g2.dispose();
        }

        @Override
        public boolean contains(int x, int y) {
            int size = Math.min(getWidth(), getHeight());
            int cx = getWidth() / 2;
            int cy = getHeight() / 2;
            int dx = x - cx;
            int dy = y - cy;
            int radius = size / 2;
            return dx * dx + dy * dy <= radius * radius;
        }

        @Override public void setContentAreaFilled(boolean b) {}
        @Override public void setBorderPainted(boolean b) {}
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            TrainRouteStationSwing frame = new TrainRouteStationSwing();
            frame.setVisible(true);
        });
    }
}